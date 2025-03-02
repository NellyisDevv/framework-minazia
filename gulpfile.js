const gulp = require('gulp')
const concat = require('gulp-concat')
const cssnano = require('gulp-cssnano')
const order = require('gulp-order')

// Task to build CSS
gulp.task('css', function () {
  return gulp
    .src('css/*.css') // All files in css/ directory
    .pipe(
      order([
        'root.css',
        'reset.css',
        'padding.css',
        'margin.css',
        'typography.css',
        'layout.css',
        'shadows.css',
        'border-radius.css',
        'max-width.css',
        'buttons.css',
      ])
    )
    .pipe(concat('style.min.css'))
    .pipe(cssnano())
    .pipe(gulp.dest('./')) // Output to theme root
})

// Watch task to monitor changes
gulp.task('watch', function () {
  gulp.watch('css/*.css', gulp.series('css'))
})

// Default task (runs css once)
gulp.task('default', gulp.series('css'))

// Optional: Watch as default (runs css once, then watches)
gulp.task('dev', gulp.series('css', 'watch'))
