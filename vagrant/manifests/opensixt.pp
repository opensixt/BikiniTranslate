$PATH = "/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin"

stage { 'pre': before => Stage['main'] }

include opensixt::network
include opensixt::tools

include opensixt::apache
include opensixt::php
include opensixt::mysql
