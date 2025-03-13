const gulp = require('gulp')
const concat = require('gulp-concat')
const cssnano = require('gulp-cssnano')
const order = require('gulp-order')
const through2 = require('through2')

// Task to generate max-width classes (max-w-5 to max-w-800)
gulp.task('generate-max-width', function () {
  return gulp
    .src('css/custom-max-width.css', { allowEmpty: true })
    .pipe(
      through2.obj(function (file, enc, cb) {
        let cssContent = ''
        // Generate max-w-5 through max-w-800
        for (let i = 1; i <= 120; i++) {
          cssContent += `.mx-w-${i * 10} { max-width: ${
            i * 10
          }px !important; }\n`
        }
        file.contents = Buffer.from(cssContent)
        cb(null, file)
      })
    )
    .pipe(gulp.dest('css'))
})

// Task to generate min-width classes (min-w-5 to min-w-800)
gulp.task('generate-min-width', function () {
  return gulp
    .src('css/custom-min-width.css', { allowEmpty: true })
    .pipe(
      through2.obj(function (file, enc, cb) {
        let cssContent = ''
        // Generate min-w-5 through min-w-800
        for (let i = 1; i <= 120; i++) {
          cssContent += `.mn-w-${i * 10} { min-width: ${
            i * 10
          }px !important; }\n`
        }
        file.contents = Buffer.from(cssContent)
        cb(null, file)
      })
    )
    .pipe(gulp.dest('css'))
})

// Task to generate min-height classes (min-h-10 to min-h-100)
gulp.task('generate-min-height', function () {
  return gulp
    .src('css/custom-min-height.css', { allowEmpty: true })
    .pipe(
      through2.obj(function (file, enc, cb) {
        let cssContent = ''
        // Generate min-h-10 through min-h-100 in 10vh increments
        for (let i = 1; i <= 10; i++) {
          cssContent += `.mn-h-${i * 10}vh { min-height: ${
            i * 10
          }vh !important; }\n`
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
  gulp.series('generate-max-width', 'generate-min-height', function () {
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
            'custom-max-width.css', // Added generated max-width file
            'custom-min-height.css', // Added generated min-height file
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
