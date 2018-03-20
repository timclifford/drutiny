#!/bin/bash -ex

ssh-add ghp-id_rsa

# sudo pip install -r docs/requirements.txt
# composer config minimum-stability dev
# composer require --prefer-stable  drutiny/plugin-distro-common drutiny/acquia drutiny/sumologic drutiny/http drutiny/plugin-drupal-7 drutiny/plugin-drupal-8 drutiny/cloudflare

./bin/build_docs
mkdocs build --clean

if [ -d ghp ]; then
  rm -rf ghp;
fi

git clone git@github.com:drutiny/drutiny.github.io.git ghp
rsync -av docs_html/ ghp/

cd ghp
git config user.name "Travis CI"
git config user.email "drutiny@travis.ci"
git add .
git commit -m "Deploy from Travis CI"
git push
