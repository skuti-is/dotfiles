alias cd..="cd .."
alias ..="cd .."
alias ...="cd ../.."
alias ....="cd ../../.."

alias df="df -h"
alias du="du -h"
alias rm="rm -v"
alias cp="cp -v"
alias tail="tail -n 1000"
alias whois="whois -h whois-servers.net"
alias hosts='sudo vim /etc/hosts'
alias todo="cd ~/Documents/todo/"
alias d="dig +short"
alias g="git"
alias v="vim"
alias a="aptitude"
alias bc="bc -l"
alias my="mycli"
alias svnu="svn st -u"
alias python="python3"
alias imagemin="imagemin -p -o 7"

# `cat` with beautiful colors. requires Pygments installed.
# sudo apt-get install python-pygments
alias c='pygmentize -O style=monokai -f console256 -g'

alias syncdb="/home/thrstn/Projects/dotfiles/scripts/bash/syncdb.sh"
alias optimimgs="/home/thrstn/Projects/dotfiles/scripts/bash/optimizeimages.sh"
alias xrsync="rsync -av --exclude '*/static/files/*' --exclude '*/static/images/*' --exclude '*/static/strevda/*' --exclude '*/static/news/*' --exclude '*/static/gallery/*' --exclude '*/static/employees/*' --exclude '*/static/tube/*' --exclude '*/static/formbuilder/*' --exclude '*/cache/*' --exclude '*/static/fyrirspurnir/*'"

alias _si="ssh root@sidux"
alias _sa="ssh salix"
alias _zorin="ssh root@zorin"
alias _pcl="ssh root@pclinuxos"
alias _b="ssh bodhi"
alias _v="ssh root@vixta"
alias _m="ssh thrstn@manjaro.stefna.is"
alias _s="ssh thrstn@saline.stefna.is"
alias _p="ssh thrstn@pinguy.stefna.is"
alias _pe="ssh thrstn@peppermint.stefna.is"
alias _d="ssh thrstn@dragora.stefna.is"
alias _r="ssh thrstn@rosa.stefna.is"
alias _a="ssh thrstn@archbang.stefna.is"
alias _an="ssh thrstn@antergos.stefna.is"
alias _c="ssh thrstn@crunchbang.stefna.is"
alias _danni="ssh danni@10.1.2.140"

alias _vma="ssh root@vma.is"

alias _180="_pcl"
alias _249="_pe"
alias _137="_sa"
alias _138="_ch"
alias _170="_b"
alias _196="_p"
alias _148="_a"
alias _155="_c"
alias _99="_s"
alias _82="_d"
alias _59="_m"

#vodafone stuff
alias _vs="ssh stefna@vs"
alias _v177="ssh stefna@v177"
alias _vpn-vodafone="sudo vpnc-connect vodafone"

alias _unak="ssh root@www.unak.is"

alias _mdb1="_z"

alias _tailmysql="tail -f /var/log/mysql/mysql.log"

alias such=git 
alias very=git 
alias wow='very status'
# $ wow 
# $ such commit 
# $ very push
