'use strict';

let gulp         = require('gulp'),
	rename       = require('gulp-rename'),
	notify       = require('gulp-notify'),
	autoprefixer = require('gulp-autoprefixer'),
	uglify       = require('gulp-uglify-es').default,
	sass         = require('gulp-sass'),
	plumber      = require('gulp-plumber'),
	checktextdomain = require('gulp-checktextdomain');

let sassSettings = {
	outputStyle: 'compressed',
	linefeed:    'crlf',
	indentType:  'tab',
	indentWidth: 1
};

//css
gulp.task('css-frontend', () => {
	return gulp.src('./assets/scss/jet-woo-builder.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( sassSettings ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('jet-woo-builder.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('css-admin', () => {
	return gulp.src('./assets/scss/admin.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( sassSettings ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('admin.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('css-editor', () => {
	return gulp.src('./assets/scss/editor.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( sassSettings ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('editor.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

// Minify JS
gulp.task( 'frontend-js-minify', function() {
	return gulp.src( './assets/js/jet-woo-builder.js' )
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( gulp.dest( './assets/js/' ) )
		.pipe( notify( 'js Minify Done!' ) );
} );

gulp.task( 'admin-js-minify', function() {
	return gulp.src( './assets/js/jet-woo-builder-admin.js' )
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( gulp.dest( './assets/js/' ) )
		.pipe( notify( 'js Minify Done!' ) );
} );

//watch
gulp.task('watch', () => {
	gulp.watch( './assets/scss/**', gulp.series( ...[ 'css-frontend', 'css-admin', 'css-editor' ] ) );

	gulp.watch( './assets/js/jet-woo-builder-admin.js', gulp.series( 'frontend-js-minify' ) );
	gulp.watch( './assets/js/jet-woo-builder-admin.js', gulp.series( 'admin-js-minify' ) );
});


