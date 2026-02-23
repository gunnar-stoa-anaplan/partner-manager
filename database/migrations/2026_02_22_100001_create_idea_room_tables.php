<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buckets (hierarchical categories)
        Schema::create('idea_buckets', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('parent_bucket_id')->nullable()->constrained('idea_buckets')->cascadeOnDelete();
            $table->tinyInteger('depth')->default(0);
            $table->string('name', 100);
            $table->string('color', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'parent_bucket_id']);
        });

        // Tags
        Schema::create('idea_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        // Ideas
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained();
            $table->string('title', 500)->nullable();
            $table->text('original_text');
            $table->string('lifecycle_state', 30)->default('spark');
            $table->timestamp('dormant_at')->nullable();
            $table->timestamp('last_activity_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'lifecycle_state']);
            $table->index(['user_id', 'last_activity_at']);
        });

        // Idea events (audit log per idea)
        Schema::create('idea_events', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('event_type', 50);
            $table->string('source', 30);
            $table->unsignedBigInteger('source_participant_id')->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->nullable();

            $table->index(['idea_id', 'created_at']);
            $table->index('event_type');
        });

        // Idea <-> Bucket pivot
        Schema::create('idea_bucket', function (Blueprint $table) {
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bucket_id')->constrained('idea_buckets')->cascadeOnDelete();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->boolean('confirmed')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->primary(['idea_id', 'bucket_id']);
        });

        // Idea <-> Tag pivot
        Schema::create('idea_tag', function (Blueprint $table) {
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('idea_tags')->cascadeOnDelete();
            $table->string('source', 30);
            $table->timestamp('created_at')->nullable();

            $table->primary(['idea_id', 'tag_id']);
        });

        // Edges (relationships between ideas)
        Schema::create('idea_edges', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('source_idea_id')->constrained('ideas')->cascadeOnDelete();
            $table->foreignId('target_idea_id')->constrained('ideas')->cascadeOnDelete();
            $table->string('edge_type', 30);
            $table->string('status', 20)->default('proposed');
            $table->string('proposed_by', 30);
            $table->decimal('confidence', 3, 2)->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_idea_id', 'target_idea_id', 'status']);
        });

        // Idea rooms (collaborative workspaces)
        Schema::create('idea_rooms', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('name', 255);
            $table->string('status', 20)->default('active');
            $table->string('agent_persona', 50)->default('general');
            $table->json('agent_config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Room messages
        Schema::create('room_messages', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('idea_room_id')->constrained('idea_rooms')->cascadeOnDelete();
            $table->string('sender_type', 30);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['idea_room_id', 'created_at']);
        });

        // Agent memories
        Schema::create('agent_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_room_id')->constrained('idea_rooms')->cascadeOnDelete();
            $table->string('memory_type', 30);
            $table->text('content');
            $table->tinyInteger('importance')->default(5);
            $table->unsignedBigInteger('source_message_id')->nullable();
            $table->string('embedding_id', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('source_message_id')->references('id')->on('room_messages');
        });

        // Artifacts
        Schema::create('idea_artifacts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('idea_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('idea_room_id')->nullable()->constrained('idea_rooms')->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('type', 30);
            $table->string('title', 255);
            $table->text('content')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_mime', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->integer('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Artifact versions
        Schema::create('idea_artifact_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artifact_id')->constrained('idea_artifacts')->cascadeOnDelete();
            $table->integer('version');
            $table->text('content')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('changed_by_type', 30);
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Snapshots
        Schema::create('idea_snapshots', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('idea_room_id')->nullable()->constrained('idea_rooms')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title', 255);
            $table->json('scope_config');
            $table->longText('content_cache');
            $table->string('access_token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('use_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // External participants
        Schema::create('idea_external_participants', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('idea_room_id')->constrained('idea_rooms')->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users');
            $table->string('participant_type', 20);
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('agent_endpoint', 500)->nullable();
            $table->text('agent_auth_token')->nullable();
            $table->json('permissions');
            $table->foreignId('snapshot_id')->nullable()->constrained('idea_snapshots')->nullOnDelete();
            $table->string('status', 20)->default('invited');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        // Audit logs
        Schema::create('idea_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('external_participant_id')->nullable();
            $table->string('actor_type', 30);
            $table->string('action', 50);
            $table->string('auditable_type', 100);
            $table->unsignedBigInteger('auditable_id');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });

        // Orchestrator queue
        Schema::create('idea_orchestrator_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('task_type', 30);
            $table->foreignId('idea_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('priority')->default(5);
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('pending');
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_orchestrator_queue');
        Schema::dropIfExists('idea_audit_logs');
        Schema::dropIfExists('idea_external_participants');
        Schema::dropIfExists('idea_snapshots');
        Schema::dropIfExists('idea_artifact_versions');
        Schema::dropIfExists('idea_artifacts');
        Schema::dropIfExists('agent_memories');
        Schema::dropIfExists('room_messages');
        Schema::dropIfExists('idea_rooms');
        Schema::dropIfExists('idea_edges');
        Schema::dropIfExists('idea_tag');
        Schema::dropIfExists('idea_bucket');
        Schema::dropIfExists('idea_events');
        Schema::dropIfExists('ideas');
        Schema::dropIfExists('idea_tags');
        Schema::dropIfExists('idea_buckets');
    }
};
