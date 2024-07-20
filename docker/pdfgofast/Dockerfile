FROM chromedp/headless-shell:latest

RUN apt-get update
RUN apt install -y wget

RUN wget https://go.dev/dl/go1.22.5.linux-amd64.tar.gz
RUN tar -C /usr/local -xzf go1.22.5.linux-amd64.tar.gz
ENV PATH=$PATH:/usr/local/go/bin

RUN mkdir /pdf
COPY ./go.mod /pdf/
COPY ./go.sum /pdf/
COPY ./main.go /pdf/
COPY ./index.html /pdf/

WORKDIR /pdf

RUN go install

ENTRYPOINT go run main.go 4444