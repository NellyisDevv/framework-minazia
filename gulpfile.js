const gulp = require('gulp')
const concat = require('gulp-concat')
const cssnano = require('gulp-cssnano')
const order = require('gulp-order')
const through2 = require('through2')

// Task to generate max-width classes (max-w-5 to max-w-800)
gulp.task('generate-max-widths', function () {
  return gulp
    .src('css/custom-max-width.css', { allowEmpty: true })
    .pipe(
      through2.obj(function (file, enc, cb) {
        let cssContent = ''
        // Generate max-w-5 through max-w-800
        for (let i = 1; i <= 30; i++) {
          cssContent += `.max-w-${i * 50} { max-width: ${i * 50}px; }\n`
        }
        file.contents = Buffer.from(cssContent)
        cb(null, file)
      })
    )
    .pipe(gulp.dest('css'))
})

// Task to build CSS
gulp.task(
  'css',
  gulp.series('generate-max-widths', function () {
    return gulp
      .src('css/*.css')
      .pipe(
        order(
          [
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
            'flexbox.css',
            'css/*.css',
          ],
          { base: './' }
        )
      )
      .pipe(concat('style.min.css'))
      .pipe(cssnano())
      .pipe(gulp.dest('./'))
  })
)

// Watch task to monitor changes
gulp.task('watch', function () {
  gulp.watch('css/*.css', gulp.series('css'))
})

// Default task
gulp.task('default', gulp.series('css'))

// Dev task
gulp.task('dev', gulp.series('css', 'watch'))
