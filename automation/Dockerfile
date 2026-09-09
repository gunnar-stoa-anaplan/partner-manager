# Official Playwright image ships Chromium + all system libraries — the reliable
# way to run on EC2 (esp. Amazon Linux, where `playwright install --with-deps`
# is unsupported). Keep the tag's Playwright version in sync with package.json.
FROM mcr.microsoft.com/playwright:v1.47.0-jammy

WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY . .

ENV HOST=0.0.0.0 PORT=3001 STATE_DIR=/app/state ARTIFACT_DIR=/app/artifacts
EXPOSE 3001
CMD ["node", "server.mjs"]
