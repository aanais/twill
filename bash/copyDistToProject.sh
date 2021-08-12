cd ../../
result=${PWD##*/}
docker exec `docker container ls | grep fpm | awk '{print $NF}'` sh -c "cd $result && php artisan twill:update"
