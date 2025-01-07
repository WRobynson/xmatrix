Ao implementar uma nova aplicação WEB.

1 - Crie uma pasta para a nova aplicação.
     - Ex.: /xmatrix/app1
2 - Crie o certificado e a chave para o subdomínio da aplicação.
    - Ex.: 
    ```openssl req -new -newkey rsa:4096 -x509 -sha256 -days 365 -nodes -out ./app1.xmatrix.com.br.crt -keyout./app1.xmatrix.com.br.key -subj "/C=BR/ST=Brasilia/L=DF/O=XMATRIX/CN=app1.xmatrix.com.br"```
3 - Mova o certificado e a chava para dentro de `/xmatrix/certs`.
4 - Altere o arquivo de configuração `nginx.conf` para adicionar o novo subdomínio.