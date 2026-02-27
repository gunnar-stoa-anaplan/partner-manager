import Alpine from 'alpinejs';
import axios from 'axios';

window.Alpine = Alpine;

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const API_BASE = '/api/v1/idea-room';

Alpine.data('chat', () => ({
    message: '',
    messages: [],
    ideas: [],
    loading: false,
    error: null,

    // Triage state
    triageActive: false,
    triageCards: [],
    triageRoomTargets: [],
    triageBucketTargets: [],
    triageRouted: {},
    triageSelectedCard: null,
    triageHoveredTarget: null,
    triageConfirming: false,

    async send() {
        if (!this.message.trim() || this.loading) return;

        const userMessage = this.message.trim();
        this.message = '';
        this.error = null;
        this.messages.push({ role: 'user', content: userMessage });
        this.loading = true;

        try {
            const history = this.messages.slice(0, -1)
                .filter(m => m.role === 'user' || m.role === 'assistant')
                .slice(-20)
                .map(m => ({ role: m.role, content: m.content }));

            const response = await axios.post(`${API_BASE}/chat`, {
                message: userMessage,
                conversation_history: history,
            }, { timeout: 120000 });

            const data = response.data;

            this.messages.push({
                role: 'assistant',
                content: data.message,
                actions: data.actions || [],
            });

            const triageAction = data.actions?.find(a => a.tool === 'suggest_triage' && a.success);
            if (triageAction) {
                this.activateTriage(triageAction.data);
            }

            if (data.actions?.some(a => a.tool === 'create_idea' || a.tool === 'update_idea_state' || a.tool === 'propose_buckets')) {
                await this.loadIdeas();
            }
        } catch (err) {
            if (err.response?.status === 503) {
                this.error = 'AI service is temporarily unavailable. Check your API key configuration.';
            } else if (err.response?.status === 401) {
                window.location.href = '/login';
                return;
            } else if (err.code === 'ECONNABORTED') {
                this.error = 'Request timed out. Try sending fewer ideas at a time.';
            } else {
                this.error = err.response?.data?.message || 'Something went wrong.';
            }
            this.messages.pop();
        } finally {
            this.loading = false;
            this.$nextTick(() => {
                const container = document.getElementById('ir-messages');
                if (container) container.scrollTop = container.scrollHeight;
            });
        }
    },

    activateTriage(data) {
        this.triageCards = (data.suggestions || []).map(s => ({
            ...s,
            idea_title: s.idea_title || s.idea_uuid,
        }));
        this.triageRoomTargets = data.room_targets || [];
        this.triageBucketTargets = data.bucket_targets || [];
        this.triageSelectedCard = null;
        this.triageHoveredTarget = null;

        const preRouted = {};
        for (const card of this.triageCards) {
            if (card.suggested_room_uuid && card.suggested_room_name) {
                preRouted[card.idea_uuid] = {
                    target_type: 'room',
                    target_id: card.suggested_room_uuid,
                    target_name: card.suggested_room_name,
                    suggested: true,
                };
            }
        }
        this.triageRouted = preRouted;
        this.triageActive = true;
    },

    onDragStart(event, ideaUuid) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', ideaUuid);
        this.triageSelectedCard = ideaUuid;
    },

    onDragEnd() { this.triageHoveredTarget = null; },
    onDragOver(targetId) { this.triageHoveredTarget = targetId; },
    onDragLeave() { this.triageHoveredTarget = null; },

    onDrop(targetType, targetId, targetName) {
        const ideaUuid = this.triageSelectedCard;
        if (!ideaUuid) return;
        this.triageRouted = {
            ...this.triageRouted,
            [ideaUuid]: { target_type: targetType, target_id: targetId, target_name: targetName },
        };
        this.triageSelectedCard = null;
        this.triageHoveredTarget = null;
    },

    toggleSelectCard(ideaUuid) {
        if (this.triageRouted[ideaUuid]) {
            const updated = { ...this.triageRouted };
            delete updated[ideaUuid];
            this.triageRouted = updated;
            return;
        }
        this.triageSelectedCard = this.triageSelectedCard === ideaUuid ? null : ideaUuid;
    },

    onTargetClick(targetType, targetId, targetName) {
        if (!this.triageSelectedCard) return;
        if (this.triageRouted[this.triageSelectedCard]) return;
        this.triageRouted = {
            ...this.triageRouted,
            [this.triageSelectedCard]: { target_type: targetType, target_id: targetId, target_name: targetName },
        };
        this.triageSelectedCard = null;
    },

    async confirmAllTriage() {
        if (this.triageConfirming) return;
        this.triageConfirming = true;

        try {
            const entries = Object.entries(this.triageRouted);
            for (const [ideaUuid, routing] of entries) {
                if (routing.target_type === 'room') {
                    await axios.post(`${API_BASE}/ideas/${ideaUuid}/route-to-room`, { room_uuid: routing.target_id });
                } else {
                    await axios.post(`${API_BASE}/ideas/${ideaUuid}/route-to-room`, { create_new: true });
                }
            }
            for (const card of this.triageCards) {
                if (!this.triageRouted[card.idea_uuid]) {
                    await axios.post(`${API_BASE}/ideas/${card.idea_uuid}/route-to-room`, { create_new: true });
                }
            }
            await this.loadIdeas();
        } catch {
            this.error = 'Failed to route some ideas. Please try again.';
        } finally {
            this.triageConfirming = false;
            this.triageActive = false;
        }
    },

    skipTriage() { this.triageActive = false; },

    get triageAllRouted() {
        return this.triageCards.length > 0 && this.triageCards.every(c => this.triageRouted[c.idea_uuid]);
    },

    async loadIdeas() {
        try {
            const response = await axios.get(`${API_BASE}/ideas`);
            this.ideas = response.data.data || [];
        } catch { /* Ideas may not exist yet */ }
    },

    formatActions(actions) {
        return actions.filter(a => a.tool !== 'suggest_triage').map(a => {
            if (a.tool === 'create_idea' && a.success) return `Created idea: "${a.data.title}"`;
            if (a.tool === 'propose_buckets' && a.success) {
                const buckets = a.data.proposals.map(p => `${p.full_path || p.bucket_name} (${Math.round(p.confidence * 100)}%)`).join(', ');
                return `Suggested buckets: ${buckets}`;
            }
            if (a.tool === 'update_idea_state' && a.success) return `Updated state: ${a.data.previous_state} → ${a.data.new_state}`;
            if (a.tool === 'add_note' && a.success) return 'Added note';
            if (a.tool === 'search_ideas' && a.success) return `Found ${a.data.total} idea(s)`;
            return `${a.tool}: ${a.success ? 'done' : a.error}`;
        });
    },

    init() { this.loadIdeas(); },
}));

Alpine.data('ideaRoom', (config) => ({
    roomUuid: config.roomUuid,
    ideaTitle: config.ideaTitle,
    ideaState: config.ideaState,
    ideaUuid: config.ideaUuid,
    ideaText: config.ideaText,
    objective: config.objective,
    buckets: config.buckets || [],
    tags: config.tags || [],

    message: '',
    messages: [],
    artifacts: [],
    memories: [],
    loading: false,
    uploading: false,
    error: null,
    showSidebar: true,
    selectedArtifact: null,

    async send() {
        if (!this.message.trim() || this.loading) return;

        const userMessage = this.message.trim();
        this.message = '';
        this.error = null;
        this.loading = true;

        this.messages.push({ sender_type: 'user', content: userMessage, uuid: 'pending' });
        this.scrollToBottom();

        try {
            const response = await axios.post(`${API_BASE}/rooms/${this.roomUuid}/messages`, {
                message: userMessage,
            }, { timeout: 120000 });

            const data = response.data;
            this.messages[this.messages.length - 1].uuid = data.user_message_uuid;

            this.messages.push({
                sender_type: 'agent',
                content: data.message,
                uuid: data.agent_message_uuid,
                metadata: { actions: data.actions || [] },
            });

            if (data.actions?.length > 0) {
                await this.refreshSidebar(data.actions);
            }
        } catch (err) {
            if (err.response?.status === 503) {
                this.error = 'AI service is temporarily unavailable.';
            } else if (err.response?.status === 401) {
                window.location.href = '/login';
                return;
            } else if (err.code === 'ECONNABORTED') {
                this.error = 'Request timed out. Try a shorter message.';
            } else {
                this.error = err.response?.data?.message || 'Something went wrong.';
            }
            this.messages.pop();
        } finally {
            this.loading = false;
            this.scrollToBottom();
        }
    },

    async uploadFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.uploading = true;
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'file');
            formData.append('title', file.name);
            await axios.post(`${API_BASE}/rooms/${this.roomUuid}/artifacts`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            await this.loadArtifacts();
        } catch {
            this.error = 'Failed to upload file.';
        } finally {
            this.uploading = false;
            event.target.value = '';
        }
    },

    async refreshSidebar(actions) {
        const toolNames = actions.map(a => a.tool);
        if (toolNames.includes('save_artifact') || toolNames.includes('update_artifact')) await this.loadArtifacts();
        if (toolNames.includes('save_memory')) await this.loadMemories();
        if (toolNames.includes('set_objective')) {
            const objAction = actions.find(a => a.tool === 'set_objective' && a.success);
            if (objAction) this.objective = objAction.data.objective;
        }
        if (toolNames.includes('update_idea_state')) {
            const stateAction = actions.find(a => a.tool === 'update_idea_state' && a.success);
            if (stateAction) this.ideaState = stateAction.data.new_state;
        }
    },

    async loadMessages() {
        try {
            const response = await axios.get(`${API_BASE}/rooms/${this.roomUuid}/messages`);
            this.messages = response.data.data || [];
            this.scrollToBottom();
        } catch { /* Room may be new */ }
    },

    async loadArtifacts() {
        try {
            const response = await axios.get(`${API_BASE}/ideas/${this.ideaUuid}`);
            // TODO: dedicated room artifacts endpoint
        } catch { /* Artifacts not yet available */ }
    },

    async loadMemories() {
        try {
            const response = await axios.get(`${API_BASE}/rooms/${this.roomUuid}/memories`);
            this.memories = response.data.data || [];
        } catch { /* Memories not yet available */ }
    },

    async deactivateMemory(memoryId) {
        try {
            await axios.delete(`${API_BASE}/rooms/${this.roomUuid}/memories/${memoryId}`);
            this.memories = this.memories.filter(m => m.id !== memoryId);
        } catch { /* Ignore */ }
    },

    formatActions(actions) {
        if (!actions) return [];
        return actions.filter(a => a.success).map(a => {
            if (a.tool === 'save_artifact') return `Created: ${a.data.title} (${a.data.type})`;
            if (a.tool === 'update_artifact') return `Updated: ${a.data.title}`;
            if (a.tool === 'add_note') return 'Added note';
            if (a.tool === 'update_idea_state') return `State → ${a.data.new_state}`;
            if (a.tool === 'save_memory') return `Saved ${a.data.memory_type}: ${a.data.content.substring(0, 50)}...`;
            if (a.tool === 'set_objective') return `Objective set: ${a.data.objective.substring(0, 60)}`;
            return `${a.tool}: done`;
        });
    },

    scrollToBottom() {
        this.$nextTick(() => {
            const container = document.getElementById('ir-room-messages');
            if (container) container.scrollTop = container.scrollHeight;
        });
    },

    init() {
        this.loadMessages();
        this.loadMemories();
    },
}));

Alpine.data('worldView', () => ({
    stats: null,
    rooms: [],
    loading: true,
    error: null,

    frameSlots: [],
    galleryImageUrl: null,
    backgroundImageUrl: null,
    aspectRatio: '4 / 3',
    currentTemplate: null,
    greyscale: false,
    galleryMode: 'static',
    videos: [],
    templateImageUrl: null,

    templates: [],
    showTemplatePicker: false,

    get arW() {
        const parts = this.aspectRatio.split('/').map(s => parseFloat(s.trim()));
        return parts[0] || 16;
    },

    get arH() {
        const parts = this.aspectRatio.split('/').map(s => parseFloat(s.trim()));
        return parts[1] || 9;
    },

    roomFilter: 'all',

    whiteboardUrl: '/idea-room/whiteboard',

    async loadStats() {
        try {
            const response = await axios.get(`${API_BASE}/dashboard/stats`);
            this.stats = response.data;
        } catch (err) {
            if (err.response?.status === 401) { window.location.href = '/login'; return; }
            this.error = 'Failed to load dashboard data.';
        }
    },

    async loadRooms() {
        try {
            const response = await axios.get(`${API_BASE}/rooms`);
            this.rooms = response.data.data || [];
        } catch (err) {
            if (err.response?.status === 401) { window.location.href = '/login'; }
        }
    },

    async loadGalleryConfig() {
        try {
            const { data } = await axios.get(`${API_BASE}/gallery/config`);
            this.frameSlots = data.frame_slots || [];
            this.galleryImageUrl = data.gallery_image_url;
            this.aspectRatio = data.aspect_ratio || '4 / 3';
            this.currentTemplate = data.template;
            this.greyscale = data.greyscale;
            this.galleryMode = data.mode || 'static';
            this.videos = data.videos || [];
            this.templateImageUrl = data.template_image_url;
            this.backgroundImageUrl = data.background_image_url || null;
        } catch (err) {
            if (err.response?.status === 401) { window.location.href = '/login'; }
        }
    },

    async loadTemplates() {
        try {
            const { data } = await axios.get(`${API_BASE}/gallery/templates`);
            this.templates = data.data || [];
        } catch { /* Non-critical */ }
    },

    async selectTemplate(slug) {
        try {
            await axios.post(`${API_BASE}/gallery/template`, { slug });
            this.showTemplatePicker = false;
            await this.loadGalleryConfig();
        } catch { /* Ignore */ }
    },

    get whiteboardSlotIndex() {
        return this.frameSlots.length - 1;
    },

    isWhiteboardSlot(slotIndex) {
        return slotIndex === this.whiteboardSlotIndex;
    },

    enterRoom(event, roomUuid) {
        event.preventDefault();
        const dest = roomUuid ? `/idea-room/rooms/${roomUuid}` : this.whiteboardUrl;
        window.location.href = dest;
    },

    activityScore(room) {
        return (room.messages_count || 0) + (room.artifacts_count || 0);
    },

    get filteredRooms() {
        let pool = this.rooms;

        if (this.roomFilter === 'personal') {
            pool = pool.filter(r => r.root_category === 'personal');
        } else if (this.roomFilter === 'business') {
            pool = pool.filter(r => r.root_category === 'business');
        }

        return pool.slice().sort((a, b) => this.activityScore(b) - this.activityScore(a));
    },

    roomForSlot(slotIndex) {
        if (this.isWhiteboardSlot(slotIndex)) return null;
        const rooms = this.filteredRooms;
        return slotIndex < rooms.length ? rooms[slotIndex] : null;
    },

    slotStyle(slot) {
        return `left:${slot.left}%;top:${slot.top}%;width:${slot.width}%;height:${slot.height}%`;
    },

    videoForSlot(slotIndex) {
        return this.videos.find(v => v.frame_index === slotIndex) || null;
    },

    framePlacard(slotIndex) {
        const room = this.roomForSlot(slotIndex);
        if (!room) return null;
        return this.placardWord(room.name);
    },

    placardWord(name) {
        const containers = new Set([
            'hub','lab','center','suite','room','system','platform','board',
            'desk','space','zone','engine','module','analyzer','manager',
            'tracker','tool','dashboard','portal','console','workbench',
        ]);
        const words = name.split(/\s+/).filter(w => w.length > 0);
        for (let i = words.length - 1; i >= 0; i--) {
            if (!containers.has(words[i].toLowerCase())) return words[i];
        }
        return words[words.length - 1];
    },

    init() {
        Promise.all([
            this.loadStats(),
            this.loadRooms(),
            this.loadGalleryConfig(),
            this.loadTemplates(),
        ]).then(() => { this.loading = false; });
    },
}));

Alpine.data('roomList', () => ({
    rooms: [],
    loading: true,
    search: '',
    statusFilter: 'all',

    async loadRooms() {
        try {
            const { data } = await axios.get(`${API_BASE}/rooms`);
            this.rooms = data.data || [];
        } catch { /* Rooms may not exist yet */ }
        this.loading = false;
    },

    get filteredRooms() {
        let pool = this.rooms;
        if (this.statusFilter !== 'all') {
            pool = pool.filter(r => r.status === this.statusFilter);
        }
        if (this.search.trim()) {
            const q = this.search.toLowerCase();
            pool = pool.filter(r =>
                r.name.toLowerCase().includes(q) ||
                (r.idea?.title || '').toLowerCase().includes(q)
            );
        }
        return pool;
    },

    init() { this.loadRooms(); },
}));

Alpine.data('ideaList', () => ({
    ideas: [],
    loading: true,
    search: '',
    stateFilter: 'all',
    sortBy: 'last_activity_at',

    async loadIdeas() {
        try {
            const { data } = await axios.get(`${API_BASE}/ideas`);
            this.ideas = data.data || [];
        } catch { /* Ideas may not exist yet */ }
        this.loading = false;
    },

    get filteredIdeas() {
        let pool = this.ideas;
        if (this.stateFilter !== 'all') {
            pool = pool.filter(i => i.lifecycle_state === this.stateFilter);
        }
        if (this.search.trim()) {
            const q = this.search.toLowerCase();
            pool = pool.filter(i => i.title.toLowerCase().includes(q));
        }
        return [...pool].sort((a, b) => {
            if (this.sortBy === 'title') return a.title.localeCompare(b.title);
            return new Date(b[this.sortBy] || 0) - new Date(a[this.sortBy] || 0);
        });
    },

    init() { this.loadIdeas(); },
}));

Alpine.data('whiteboard', () => ({
    text: '',
    submitting: false,
    feedback: null,
    errorMessage: null,
    captureCount: 0,

    handleKeydown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.submit();
        }
    },

    async submit() {
        const message = this.text.trim();
        if (!message || this.submitting) return;

        this.submitting = true;
        this.feedback = null;
        this.errorMessage = null;

        try {
            await axios.post(`${API_BASE}/chat`, {
                message,
                conversation_history: [],
            }, { timeout: 120000 });

            this.captureCount++;
            this.text = '';
            this.feedback = 'success';

            setTimeout(() => {
                this.feedback = null;
                this.$nextTick(() => this.$refs.whiteboardInput?.focus());
            }, 1800);
        } catch (err) {
            if (err.response?.status === 503) {
                this.errorMessage = 'AI service unavailable. Try again shortly.';
            } else if (err.response?.status === 401) {
                window.location.href = '/login';
                return;
            } else {
                this.errorMessage = err.response?.data?.message || 'Failed to capture idea.';
            }
            this.feedback = 'error';

            setTimeout(() => {
                this.feedback = null;
                this.errorMessage = null;
            }, 3000);
        } finally {
            this.submitting = false;
        }
    },
}));

Alpine.start();
