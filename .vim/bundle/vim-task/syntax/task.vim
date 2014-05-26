if exists("b:current_syntax")
  finish
endif

syntax keyword taskKeyword New new Working working Done done Todo TODO todo bug Bug

syntax match taskWorkingIcon "^-" contained
syntax match taskWorkingIcon "^\s*-" contained
syntax match taskDoneIcon "^✓" contained
syntax match taskDoneIcon "^\s*✓" contained
syntax match taskCommentIcon "^#" contained
syntax match taskCommentIcon "^\s*#" contained
syntax match taskWarningIcon "^!" contained
syntax match taskWarningIcon "^\s*!" contained

syntax match taskWorkingItem "^-.*" contains=taskWorkingIcon,taskKeyword
syntax match taskWorkingItem "^\s*-.*" contains=taskWorkingIcon,taskKeyword
syntax match taskDoneItem "^✓.*" contains=taskDoneIcon,taskKeyword
syntax match taskDoneItem "^✓.*" contains=taskDoneIcon,taskKeyword
syntax match taskCommentItem "^*#.*" contains=taskCommentIcon,taskKeyword
syntax match taskCommentItem "^\s*#.*" contains=taskCommentIcon,taskKeyword
syntax match taskWarningItem "^*!.*" contains=taskWarningIcon,taskKeyword
syntax match taskWarningItem "^\s*!.*" contains=taskWarningIcon,taskKeyword

highlight taskKeyword guifg=#96CBFE guibg=NONE gui=NONE ctermfg=blue ctermbg=NONE cterm=NONE

highlight taskWorkingItem guifg=#f6f3e8 guibg=NONE gui=NONE ctermfg=NONE ctermbg=NONE cterm=NONE
highlight taskDoneItem guifg=#A8FF60 guibg=NONE gui=italic ctermfg=green ctermbg=NONE cterm=NONE
highlight taskCommentItem guifg=#AAAAAA guibg=NONE gui=NONE ctermfg=gray ctermbg=NONE cterm=NONE
highlight taskWarningItem guifg=#000000 guibg=#CC0000 gui=NONE ctermfg=black ctermbg=red cterm=NONE

highlight taskWorkingIcon guifg=#FF6C60 guibg=NONE gui=NONE ctermfg=red ctermbg=NONE cterm=NONE
highlight taskDoneIcon guifg=#A8FF60 guibg=NONE gui=italic ctermfg=green ctermbg=NONE cterm=NONE
highlight taskCommentIcon guifg=#AAAAAA guibg=NONE gui=NONE ctermfg=gray ctermbg=NONE cterm=NONE
highlight taskWarningIcon guifg=#000000 guibg=#CC0000 gui=NONE ctermfg=black ctermbg=RED cterm=NONE

syntax match sectionTitleLine "^.*:\s*$" contains=sectionTitle
syntax match sectionTitle "\S.*:\s*$"
highlight sectionTitle guifg=#96CBFE guibg=NONE gui=bold,underline ctermfg=blue ctermbg=NONE cterm=bold,underline

syntax match subSectionTitleLine "^.*;\s*$" contains=subSectionTitle
syntax match subSectionTitle "\S.*;\s*$"
highlight subSectionTitle guifg=#C9FFE5 guibg=NONE gui=underline ctermfg=cyan ctermbg=NONE cterm=underline

let b:current_syntax = "task"

