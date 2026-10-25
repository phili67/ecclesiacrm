FROM collabora/code:latest

# Trust the local mkcert CA so Collabora can call the CRM over HTTPS (dev only)
USER root
COPY rootCA.pem /usr/local/share/ca-certificates/mkcert-dev.crt
RUN update-ca-certificates
