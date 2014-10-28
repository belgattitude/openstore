module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
		concat: {   
			// 2. Configuration for concatinating files goes here.
			dist: {
				src: [
					'vendor/jquery/dist/jquery.js', 
					'vendor/bootstrap/dist/js/bootstrap.js', 
					'vendor/select2/select2.js', 
					'vendor/typeahead.js/dist/typeahead.bundle.js', 
					'js/handlebars/handlebars.js'
					//'js/global.js'  // This specific file
				],
				dest: 'dist/js/production.js',
			}
		},
		uglify: {
			build: {
				src: 'dist/js/production.js',
				dest: 'dist/js/production.min.js'
			}
		},
		sass: {
			dist: {
				options: {
					//style: 'compressed'
				},
				files: {
					'dist/css/global.css': 'css/global.scss',
                                        'dist/css/catalog.css': 'css/catalog.scss',
					'dist/css/typeahead-bootstrap.css': 'css/typeahead-bootstrap.scss'
				}				
			} 
		},
		
                autoprefixer: {
                    dist: {
                        files: {
                            'dist/css/global.prefixed.css': 'dist/css/global.css',
                            'dist/css/catalog.prefixed.css': 'dist/css/catalog.css',
                            'dist/css/typeahead-bootstrap.prefixed.css': 'dist/css/typeahead-bootstrap.css'
                        }
                    }
                },
		
		cssrb: {
		  main: {
			src: 'vendor/select2/select2.css',
			dest: 'dist/css/select2.css',
			options: {
			  old_base: 'vendor/select2/',
			  new_base: 'dist/css',
			  //patterns:  {'^/images': ''},
			  copy: true
			},
		  },
		},
		cssmin: {
			"main": {
			  options: {
				banner: '/* openstore theme */'
			  },
			  files: {
				'dist/css/global.min.css': [
                                        'vendor/select2/select2.css',
                                        'vendor/select2/select2-bootstrap.css',
					'dist/css/global.prefixed.css',
                                        'dist/css/catalog.prefixed.css',
					'dist/css/typeahead-bootstrap.prefixed.css'
				  ]
			  }
			},
			"bootstrap-default": {
			  files: {
				 'dist/css/bootstrap-default.min.css': [ 'vendor/bootstrap/dist/css/bootstrap.min.css' ]
			  }
			},
			"bootstrap-united": {
			  files: {
				 'dist/css/bootstrap-united.min.css': [ 'vendor/bootswatch/united/bootstrap.min.css' ]
			  }
			},
			"bootstrap-cerulean": {
			  files: {
				 'dist/css/bootstrap-cerulean.min.css': [ 'vendor/bootswatch/cerulean/bootstrap.min.css' ]
			  }
			},
			"bootstrap-slate": {
			  files: {
				 'dist/css/bootstrap-slate.min.css': [ 'vendor/bootswatch/slate/bootstrap.min.css' ]
			  }
			},
			
			'bootstrap-flatly': {
			  files: {
				 'dist/css/bootstrap-flatly.min.css': [ 'vendor/bootswatch/flatly/bootstrap.min.css' ]
			  }
			},
			
			'bootstrap-yeti': {
			  files: {
				 'dist/css/bootstrap-yeti.min.css': [ 'vendor/bootswatch/yeti/bootstrap.min.css' ]
			  }
			},
			'bootstrap-cyborg': {
			  files: {
				 'dist/css/bootstrap-cyborg.min.css': [ 'vendor/bootswatch/cyborg/bootstrap.min.css' ]
			  }
			}
			
			
		},
		watch: {
			options: {
				livereload: true,
			},			
			scripts: {
				files: ['js/*.js'],
				tasks: ['concat', 'uglify', 'sass', 'autoprefixer', 'cssrb', 'cssmin'],
				options: {
					spawn: false,
				},
			},
			css: {
				files: ['css/*.scss'],
				tasks: ['sass'],
				options: {
					spawn: false,
				}
			}			
		}		

    });

    // 3. Where we tell Grunt we plan to use this plug-in.
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-cssrb');
    grunt.loadNpmTasks('grunt-check-modules');
    grunt.loadNpmTasks('grunt-contrib-copy');
    //grunt.loadNpmTasks('grunt-yui-compressor');

    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    grunt.registerTask('default', ['concat', 'uglify', 'sass', 'autoprefixer', 'cssrb', 'cssmin']);

};
