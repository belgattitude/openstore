module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

		concat: {   
			// 2. Configuration for concatinating files goes here.
			dist: {
				src: [
					'vendor/jquery/dist/jquery.js', // All JS in the libs folder
					'vendor/bootstrap/dist/js/bootstrap.js', // All JS in the libs folder
					//'js/global.js'  // This specific file
				],
				dest: 'build/js/production.js',
			}
		},
		uglify: {
			build: {
				src: 'build/js/production.js',
				dest: 'build/js/production.min.js'
			}
		},
		sass: {
			dist: {
				options: {
					//style: 'compressed'
				},
				files: {
					'build/css/global.css': 'css/global.scss'
				}
			} 
		},
        autoprefixer: {
            dist: {
                files: {
                    'build/css/global.prefixed.css': 'build/css/global.css'
                }
            }
        },
		/*
		cssmin: {
		  add_banner: {
			options: {
			  banner: 'blah'
			},
			files: {
			  'build/css/global.min.css': ['build/css/global.prefixed.css']
			}
		  }
		},*/
		cssmin: {
			dist: {
				'src': ['build/css/global.prefixed.css'],
				'dest': 'build/css/global.min.css'
			}
		},
		watch: {
			options: {
				livereload: true,
			},			
			scripts: {
				files: ['js/*.js'],
				tasks: ['concat', 'uglify', 'sass', 'autoprefixer', 'cssmin'],
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
	//grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-yui-compressor');

    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    grunt.registerTask('default', ['concat', 'uglify', 'sass', 'autoprefixer', 'cssmin']);

};
