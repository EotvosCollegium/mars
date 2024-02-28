FROM ubuntu:23.10

WORKDIR /workspace

# Install PHP and other dependencies
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y wget tar xz-utils iputils-ping composer poppler-utils cups-client cm-super \
                          libzip-dev libpng-dev libonig-dev libcurl4-openssl-dev libxml2-dev \
                          php8.2-cli php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
                          texlive-latex-base texlive-latex-extra texlive-lang-european \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js
RUN wget -nv -O node.tar.xz https://nodejs.org/dist/v20.9.0/node-v20.9.0-linux-x64.tar.xz && \
    tar -xf node.tar.xz && \
    rm node.tar.xz
ENV PATH=$PATH:/workspace/node-v20.9.0-linux-x64/bin

WORKDIR /workspace/mnt

CMD ["/usr/bin/sleep", "infinity"]
