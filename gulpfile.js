var gulp = require('gulp');
var babel = require('gulp-babel');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var eslint = require('gulp-eslint');
var notify = require('gulp-notify');
var minify = require('gulp-minify');

var jsFiles = {
    vendor: [
        'resources/scripts/lib/react/react-with-addons.js',
        'public/js/select2.full.js',
        'resources/scripts/lib/underscore.js',
        'resources/scripts/lib/backbone.js',
        'resources/scripts/lib/director.min.js',
        'resources/scripts/lib/pdfjs/pdf.js',
        'resources/scripts/lib/jquery-ui.min.js',
        'resources/scripts/lib/jbox.js'
    ],
    source: [
        'resources/scripts/contract.view/views/react.pdf.js',
        'resources/scripts/contract.view/views/react.waypoint.js',
        'resources/scripts/contract.view/views/text.view.js',
        'resources/scripts/contract.view/views/text.search.js',
        'resources/scripts/contract.view/views/annotations.view.js',
        'resources/scripts/contract.view/views/metadata.view.js',
        'resources/scripts/contract.view/rc.utils.js',
        'resources/scripts/contract.view/models/pages.js',
        'resources/scripts/contract.view/models/annotations.js',
        'resources/scripts/contract.view/models/search.js',
        'resources/scripts/contract.view/models/metadata.js',
        'resources/scripts/contract.view/models/contract.js',
        'resources/scripts/contract.view/models/pdf.js',
        'resources/scripts/lib/annotator/annotator-full.min.js',
        'resources/scripts/contract.view/annotation/annotator.utils.js',
        'resources/scripts/contract.view/annotation/rc.annotator.js',
        'resources/scripts/contract.view/annotation/annotator.plugin.categories.js',
        'resources/scripts/contract.view/annotation/annotator.plugin.viewer.js',
        'resources/scripts/contract.view/annotation/annotator.plugin.event.js',
        'resources/scripts/contract.view/annotation/annotator.plugin.article_reference.js',
        'resources/scripts/contract.view/annotation/annotator.plugin.parentannotation.js',
        'resources/scripts/lib/pdf-annotator.js'
    ],
    main: [
        'resources/scripts/contract.view/views/pdf.view.js',
        'resources/scripts/contract.view/views/main.view.js'
    ],
    review: [
        'resources/scripts/contract.view/views/pdf.view.for.text.edit.js',
        'resources/scripts/contract.view/views/text.edit.js',
        'resources/scripts/contract.view/views/review.main.view.js'
    ]
};

gulp.task('eslint', function () {
    return gulp.src(jsFiles.source)
        .pipe(eslint({
            baseConfig: {
                "ecmaFeatures": {
                    "jsx": true
                }
            }
        }))
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

gulp.task('js-main', function () {
    return gulp.src(jsFiles.vendor.concat(jsFiles.source).concat(jsFiles.main))
        .pipe(sourcemaps.init())
        .pipe(babel({
            only: [
                'resources/scripts/contract.view/views'
            ],
            compact: false
        }))
        .pipe(concat('app.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(minify({
            ext: {
                src: '-debug.js',
                min: '.js'
            }
        }))
        .pipe(gulp.dest('public/assets/js'));
});

gulp.task('js-review', function () {
    return gulp.src(jsFiles.vendor.concat(jsFiles.source).concat(jsFiles.review))
        .pipe(sourcemaps.init())
        .pipe(babel({
            only: [
                'resources/scripts/contract.view/views'
            ],
            compact: false
        }))
        .pipe(concat('review.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(minify({
            ext: {
                src: '-debug.js',
                min: '.js'
            }
        }))
        .pipe(gulp.dest('public/assets/js'));
});

gulp.task('concat', ['eslint', 'js-main', 'js-review']);

gulp.task('watch', function () {
    gulp.watch('resources/scripts/contract.view/**/*.{js,jsx}', ['concat']);
});

gulp.task('build', ['concat']);

gulp.task('default', ['build', 'watch']);
