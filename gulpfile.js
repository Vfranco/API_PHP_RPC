/**
* ================================
*   Dependencias
* ================================
*/
var gulp    = require('gulp'),
    concat  = require('gulp-concat'),
    uglify  = require('gulp-uglify');    

/**
 * ===============================
 *  Tareas
 * ===============================
 */
gulp.task('controllers', async function(){
    gulp.src('src/Scripts/controllers/*.js')
    .pipe(concat('cparking.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('src/Scripts/production/'))    
});

gulp.task('services', async function(){
    gulp.src('src/Scripts/services/*.js')
    .pipe(concat('cparking-services.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('src/Scripts/production/'))    
});