set :application, "BikiniTranslate"
set :domain,      "localhost"
set :deploy_to,   "/var/www/www.bikinitranslate.org"
set :app_path,    "app"

set :scm,         :git
set :repository,  "git@github.com:opensixt/BikiniTranslate.git"

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set :use_sudo, false
set :keep_releases, 3
#set :symfony_env_prod, "prod"
set :deploy_via, :remote_cache
set :interactive_mode, false

set :shared_files, ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"]

set :use_composer, true
set :update_vendors, true

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL
