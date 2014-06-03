module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
	  pot: {
	    options: {
	      text_domain: 'gf_terms_conditions',
	      dest: 'languages/',
	      keywords: ['gettext', '__', '_e']
	    },
	    files: {
	      src:  [ '**/*.php' ], //Parse all php files
	      expand: true
	    },
	  },
	})

  // Load pot plugin
  grunt.loadNpmTasks('grunt-pot');

  // Default task(s).
  grunt.registerTask('default', ['pot']);

};

