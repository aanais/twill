cd ../../
docker exec `docker container ls | grep fpm | awk '{print $NF}'` php artisan twill:update
