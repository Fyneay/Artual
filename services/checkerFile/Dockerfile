FROM golang:1.24.0 as builder
LABEL authors="fyneay"
WORKDIR /app

COPY go.mod go.sum ./

RUN go mod download

COPY . .

RUN CGO_ENABLED=0 GOOS=linux go build -o ./checkerFile

FROM alpine:3.17

COPY --from=builder  /app .

EXPOSE 9090

CMD ["./checkerFile"]