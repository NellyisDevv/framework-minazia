const gulp = require('gulp')
const concat = require('gulp-concat')
const cssnano = require('gulp-cssnano')
const order = require('gulp-order')
const through2 = require('through2') // New dependency for generating classes

// Task to generate max-width classes (max-w-5 to max-w-800)
gulp.task('generate-max-widths', function () {
  return gulp
    .src('css/custom-max-width.css', { allowEmpty: true }) // Source file (can be empty or non-existent)
    .pipe(
      through2.obj(function (file, enc, cb) {
        let cssContent = ''
        // Generate max-w-5 through max-w-800
        for (let i = 1; i <= 800; i++) {
          cssContent += `.max-w-${i} { max-width: ${i}px; }\n`
        }
        file.contents = Buffer.from(cssContent) // Replace file content with generated CSS
        cb(null, file)
      })
    )
    .pipe(gulp.dest('css')) // Save to css/ folder for inclusion in main task
})

// Task to build CSS
gulp.task(
  'css',
  gulp.series('generate-max-widths', function () {
    return gulp
      .src('css/*.css') // All files in css/ directory
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
            'max-width.css', // Generated file included here
            'buttons.css',
            'flexbox.css',
            'css/*.css', // Wildcard for remaining files
          ],
          { base: './' }
        )
      )
      .pipe(concat('style.min.css'))
      .pipe(cssnano()) // Minifies the output, including generated classes
      .pipe(gulp.dest('./')) // Output to theme root
  })
)

// Watch task to monitor changes
gulp.task('watch', function () {
  gulp.watch('css/*.css', gulp.series('css'))
})

// Default task (runs css once)
gulp.task('default', gulp.series('css'))

// Optional: Watch as default (runs css once, then watches)
gulp.task('dev', gulp.series('css', 'watch'))
