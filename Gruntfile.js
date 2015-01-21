'use strict';

module.exports = function (grunt) {
    /**
     * load all grunt tasks matching the `grunt-*` pattern
     * This means any tasks that DO NOT match the `grunt-*` pattern
     * need to be added here
     */
    require('load-grunt-tasks')(grunt);

    // Show elapsed time at the end
    require('time-grunt')(grunt);

    grunt.util.linefeed = '\n';

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        banner: {
            compact: '/*! <%= pkg.name %> <%= pkg.version %> | <%= pkg.license %> */',
            full: '/*!\n' +
            ' * <%= pkg.name %> || <%= pkg.title %> - v<%= pkg.version %> \n' +
            ' * Copyright <%= grunt.template.today("yyyy") %> <%= pkg.author %>\n' +
            '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
            ' * Licensed under <%= pkg.license.type %> (<%= pkg.license.url %>)\n' +
            ' */\n'
        },

        datetime: Date.now(),

        // every 'clean' does a dump of the existing 'dist' folder content
        clean: ['dist/*'],

        watch: {

            script: {
                files: '<%= jshint.src %>',
                tasks: ['jshint', 'jscs']
            },

            sync: {
                files: ['src/*'],
                tasks: ['watch']
            }
        },

        sync: {
            main: {
                files: [{
                    cwd: 'src',
                    src: '**', /* Include everything */
                    dest: 'dist',
                }],
                verbose: true // Display log messages when copying files
            }
        },

        jsonlint: {
            configFiles: {
                src: ['jscs.json']
            }
        },

        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            src: 'queueTest.js'
        },

        copy: {
            files: {
                cwd: 'src',  // set working folder / root to copy
                src: '**/*', // copy all files and subfolders
                dest: 'dist/', // destination folder
                expand: true // required when using cwd
            },
            bower: {
                src: 'bower_components/**/*',
                dest: 'dist/'
            }
        }
    });

    grunt.registerTask('dist', ['jshint', 'clean', 'copy']);
    grunt.registerTask('lint', ['jshint']);
    grunt.registerTask('default', ['dist', 'lint']);
};