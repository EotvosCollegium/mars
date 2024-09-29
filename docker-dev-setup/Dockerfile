FROM ubuntu:23.10

WORKDIR /workspace

# Install PHP and other dependencies
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y curl tar xz-utils iputils-ping composer poppler-utils cups-client cm-super \
                          libzip-dev libpng-dev libonig-dev libcurl4-openssl-dev libxml2-dev \
                          php8.2-cli php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
                          texlive-latex-base texlive-latex-extra texlive-lang-european \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js
RUN curl -sL 'https://deb.nodesource.com/setup_20.x' | bash - \
    && apt-get -y install nodejs \
    && ln -s /usr/bin/nodejs /usr/local/bin/node

WORKDIR /workspace/mars

CMD ["/usr/bin/sleep", "infinity"]
