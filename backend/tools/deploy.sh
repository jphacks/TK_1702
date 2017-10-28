#!/bin/bash -e

_rsync_args=( \
	-e ssh \
	--verbose \
	--recursive \
	--update \
	--perms \
	--delete \
	--links --copy-unsafe-links \
	--checksum \
	--exclude=/config/*.yaml \
	--exclude=.git \
	${RSYNC_ARGS-} \
)

if ${CHAT_ENV+:} false; then
	:
else
	echo "specify CHAT_ENV" >&2
	exit 1
fi

cd "$(dirname "$0")/.."
source tools/common.sh

if [ $# -ne 1 ]; then
	die "specify deploy target"
fi

_deploy_target="$1"

# check dirty/untracked files
[[ $(git diff HEAD --shortstat 2> /dev/null | tail -n1) != "" ]] && _git_dirty=1
_git_untracked=$(git status --porcelain 2>/dev/null| grep "^??" | wc -l)
${hogehoge}
if [ ${_git_untracked} -gt 0 -o ${_git_dirty-0} -gt 0 ]; then
	print_error "You have dirty/untracked files. Please commit/ignore them first."
	print_error " note: you can ignore files locally with .git/info/exclude"
	die "precondition failure"
fi

print_warning "deployer: running tests..."
exec_cmd tools/test.sh

print_warning "deployer: preparing deploy files..."
test -d deploy-worktree.tmp && rm -rf deploy-worktree.tmp/
exec_cmd git worktree add deploy-worktree.tmp HEAD
cd deploy-worktree.tmp
exec_cmd tools/prepare.sh

print_warning "deployer: checking updated files..."
rsync --dry-run "${_rsync_args[@]}" ./ "${_deploy_target}"

print_warning "deployer: consider deploy files! Enter press if you want to proceed"
read
print_warning "deployer: deploying..."
rsync "${_rsync_args[@]}" ./ "${_deploy_target}"

print_warning "deployer: cleaning files..."
cd ..
rm -rf deploy-worktree.tmp
git worktree prune

print_warning "deployer: done!"
