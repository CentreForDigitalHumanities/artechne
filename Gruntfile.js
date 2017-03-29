module.exports = function (grunt) {
	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			build: {
				files: {
					'js/min/artechne.min.js': ['js/search.js'],
				}
			}
		},
		sass: {
			build: {
				expand: true,
				cwd: 'css/',
				src: ['*.scss'],
				dest: 'css/compiled/',
				ext: '.css'
			}
		},
		cssmin: {
			build: {
				expand: true,
				cwd: 'css/compiled/',
				src: ['*.css'],
				dest: 'css/min/',
				ext: '.min.css'
			}
		},
		watch: {
			js: {
				files: ['js/*.js'],
				tasks: ['uglify']
			},
			css: {
				files: ['css/*.scss'],
				tasks: ['sass', 'cssmin']
			}
		}
	});

	// Load the plugins
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Registering tasks
	grunt.registerTask('default', ['uglify', 'sass', 'cssmin']);
};
