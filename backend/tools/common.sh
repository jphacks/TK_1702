print_error() {
	printf "\e[41m%s\e[0m\n" "$*" >&2
}

print_warning() {
	printf "\e[43;30m%s\e[0m\n" "$*" >&2
}

print_cmd() {
	#printf "\e[46;30;1m%s\e[0m\n" "$*"
	printf "\e[36;1m%s\e[0m\n" "$*" >&2
}

print_message() {
	printf "\e[36m%s\e[0m\n" "$*" >&2
}

die() {
	print_error "$@"
	exit 1
}

exec_cmd() {
	print_cmd "> $@"
	"$@" || die "failed; cwd=$(pwd)"
}

_pushd() {
	pushd "$*" >/dev/null 2>&1 || die "pushd $* failed"
}

_popd() {
	popd >/dev/null 2>&1 || die "popd failed"
}

env_if() {
	local _env if_true if_false
	_env=$1
	if_true=$2
	if_false=${3-}
	if [ "x${CHAT_ENV}" = "x${_env}" ]; then
		echo "${if_true}"
	else
		echo "${if_false}"
	fi
}

if [ "x${CHAT_ENV-}" = x ]; then
	CHAT_ENV=dev
fi
case ${CHAT_ENV} in
	dev)
		CHAT_DB_SUFFIX=_dev
		;;
	stg)
		CHAT_DB_SUFFIX=_stg
		;;
	prod)
		CHAT_DB_SUFFIX=_prod
		;;
	*)
		die "CHAT_ENV must be one of {dev,stg,prod}"
		;;
esac

export CHAT_ENV
export CHAT_DB_SUFFIX
