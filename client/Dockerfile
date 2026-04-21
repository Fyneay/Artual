FROM node:22-alpine

WORKDIR /app

COPY package*.json ./

RUN if [ -f package.json ]; then npm install; fi

COPY . .

EXPOSE 5173

CMD ["sh", "-c", "if [ ! -f package.json ]; then npm create svelte@latest . -- --template skeleton --types typescript --no-prettier --no-eslint --no-playwright --no-vitest && npm install; fi && npm install && npm run dev -- --host 0.0.0.0"]
