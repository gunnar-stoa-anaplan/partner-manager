# Knowledge Base

> [!NOTE]
> Available to: CoE, Administrator. Requires the `knowledge_base_admin` feature flag for management.

The Knowledge Base is a centralized document repository that powers AI-driven search and contextual assistance across the platform. Documents are ingested, chunked, and embedded as vectors, enabling AI agents to search organizational knowledge semantically.

## Dashboard

The Knowledge Base Dashboard displays:

- **Total documents** in the repository
- **Completed** — documents successfully processed and searchable
- **Pending** — documents queued for processing
- **Failed** — documents that encountered errors during ingestion
- **Total chunks** — the number of text segments extracted across all documents

## Documents

### Browsing Documents

Navigate to **Strategy & CoE > Knowledge Base > Documents** to see all ingested content. Filter by status (completed, pending, failed) to manage the repository.

### Document Detail

Click any document to view:

- **Metadata** — file name, source, ingestion date, status
- **Chunks** — the extracted text segments that have been embedded. Each chunk shows its content and position within the source document
- **Re-ingest** — trigger reprocessing if the source has been updated or if the initial ingestion failed

## Ingestion

Navigate to **Strategy & CoE > Knowledge Base > Ingest** to add new documents.

### Upload Methods

| Method | Description |
|--------|-------------|
| **File Upload** | Upload individual files (PDF, DOCX, TXT) directly through the browser |
| **Directory Scan** | Point to a local filesystem directory and ingest all supported files within it |
| **Storage Disk** | Connect to an S3-compatible or other configured storage disk and ingest files from cloud storage |

### How Ingestion Works

1. Documents are uploaded or referenced from their source
2. Text is extracted and split into chunks (segments of manageable size)
3. Each chunk is embedded as a vector using a language model (1536-dimension embeddings)
4. Vectors are stored in the knowledge base database for similarity search
5. AI agents can then query the knowledge base to find relevant content

### Re-ingestion

If a document has been updated at its source, or if you need to reprocess with different settings:

1. Navigate to the document detail page
2. Click **Re-ingest**
3. The system re-extracts, re-chunks, and re-embeds the content
4. Previous embeddings for this document are replaced

## Integration with AI Agents

The Knowledge Base is the foundation for AI-powered features across the platform:

- **Product Expert Agents** search the knowledge base when answering product-specific questions
- **Partner Advisor** references organizational knowledge when generating partner insights
- **Enablement Designer** draws on the knowledge base when recommending training content

See [AI Agents](ai-agents) for details on configuring which agents have access to which knowledge base content.

> [!IMPORTANT]
> The quality of AI agent responses depends directly on the quality and completeness of the knowledge base. Keep documents current — especially when products are updated, new features are released, or implementation methodologies change.
