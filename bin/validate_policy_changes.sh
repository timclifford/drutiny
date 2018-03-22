#!/bin/bash -xe

MAIN_BRANCH=$1
MERGING_BRANCH=$2

POLICIES=`git log --stat --oneline $MAIN_BRANCH..$MERGING_BRANCH | grep ' | ' | grep 'policy.yml' | awk '{print $1}'`

for policy in $POLICIES; do
	name=`cat $policy | egrep '^name: ' | awk '{print $2}' | head -1`
	./bin/drutiny policy:info $name -vvv
done
