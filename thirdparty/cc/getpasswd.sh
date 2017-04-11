security find-generic-password -w -a Chrome -s Chrome Safe Storage
# From Python:
python -c "from subprocess import PIPE, Popen; print(Popen(['security', 'find-generic-password', '-w', '-a', 'Chrome', '-s', 'Chrome Safe Storage'], stdout=PIPE).stdout.read().strip())"