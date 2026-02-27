# AI Agents

> [!NOTE]
> Available to: CoE, Administrator. Requires `ai_agents_admin` for configuration, `ai_agents_user` for end-user access to AI features.

AI Agents provide contextual, knowledge-driven assistance throughout the platform. They are powered by large language models and grounded in your organization's [Knowledge Base](knowledge-base) to deliver accurate, domain-specific responses.

## Agent Types

Partner Manager includes several specialized agent personas:

### Product Expert Agents

Each product area can have a dedicated expert agent that answers questions about that specific product line. Product experts:

- Search the Knowledge Base for relevant documentation
- Answer implementation questions, best practices, and troubleshooting queries
- Provide context-aware responses grounded in your organization's actual documentation

### Partner Advisor

The Partner Advisor analyzes partner data and provides natural-language insights:

- Health assessment summaries
- Recommended next actions based on partner metrics
- Trend analysis and risk identification

The Advisor is accessible from the **Advisor** view in the [Partner Dashboard](partner-dashboard) analysis tools.

### Enablement Designer

The Enablement Designer helps create training content:

- Auto-generate workshop structures from a topic, product area, and target audience
- Suggest session outlines, learning objectives, and exercises
- Available when creating [Enablement Events](enablement-events) and [Workshops](workshops)

## Configuring Agents

Navigate to **Strategy & CoE > AI Agents** to manage agent configuration.

### Agent Settings

For each agent, you can configure:

- **Persona** — The agent's identity, expertise area, and communication style
- **Knowledge Base Bindings** — Which knowledge base documents the agent can search
- **Tools** — The capabilities available to the agent (e.g. knowledge base search, partner data access)

### Enabling and Disabling

Agents are controlled by two feature flags:

| Flag | Controls |
|------|----------|
| `ai_agents_admin` | Access to agent configuration screens |
| `ai_agents_user` | End-user access to AI-powered features (Advisor, Idea Room chat, etc.) |

Both flags can be activated through [Feature Activation](feature-activation).

## Where AI Appears

AI-powered features surface throughout the platform when agents are enabled:

| Feature | AI Capability |
|---------|--------------|
| **Idea Room** | AI-assisted discussion in rooms |
| **Partner Advisor** | Natural-language partner health insights |
| **Enablement Events** | AI-generated workshop designs |
| **Knowledge Base** | Semantic search across organizational documents |

> [!TIP]
> Start by building a comprehensive Knowledge Base before enabling AI agents for end users. Agents are only as good as the knowledge they can access.
