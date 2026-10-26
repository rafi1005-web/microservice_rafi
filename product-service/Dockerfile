FROM node:22

WORKDIR /app

RUN apt-get update && apt-get install -y wait-for-it

COPY . .

EXPOSE 3000

CMD ["sh", "-c", "npm install --no-audit --no-fund && wait-for-it product-db:3306 --timeout=60 -- npm start"]