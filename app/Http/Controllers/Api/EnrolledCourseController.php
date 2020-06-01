<?php

namespace App\Http\Controllers\Api;

use App\Models\EnrolledCourse;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrolledCourseController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        EnrolledCourse::create($request->all());
        return response()->json(['message'=>'Registered successfully!'], 200);
    }

    /**
     * Display the student resource by name and email linking with your courses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function readStudent(Request $request)
    {

        $student = Student::where([['students.name', 'like', '%'.$request->name.'%'],['students.email', 'like', '%'.$request->email.'%']])->first();
        $enrollement = Enrollment::where('student_id', '=', $student->id)->first();
        $enrolled_courses = EnrolledCourse::where('enrollment_id', '=', $enrollement->id)->get();
        $courses = [];
        foreach ($enrolled_courses as $enrolledcourse){
            $courses[] = Course::where('id', '=', $enrolledcourse->course_id)->first();
        }
        $student->courses = $courses;
        return $student;
    }

    /**
     * A request that reports the total number of students by age group separated by course and gender.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function readAgeRange(Request $request)
    {
        $smaller_15 = $this->customQueryAgeRange('NOW()', 'DATE_SUB(NOW(), INTERVAL 14 YEAR)');
        $between_15_18 = $this->customQueryAgeRange('DATE_SUB(NOW(), INTERVAL 15 YEAR)', 'DATE_SUB(NOW(), INTERVAL 18 YEAR)');
        $between_19_24 = $this->customQueryAgeRange('DATE_SUB(NOW(), INTERVAL 19 YEAR)', 'DATE_SUB(NOW(), INTERVAL 24 YEAR)');
        $between_25_30 = $this->customQueryAgeRange('DATE_SUB(NOW(), INTERVAL 25 YEAR)', 'DATE_SUB(NOW(), INTERVAL 30 YEAR)');
        $greater_30 = $this->customQueryAgeRange('NOW()', 'DATE_SUB(NOW(), INTERVAL 31 YEAR)', true);

        return ['smaller_15' => $smaller_15, 'between_15_18' => $between_15_18, 
            'between_19_24' => $between_19_24, 'between_25_30' => $between_25_30, 
            'greater_30' => $greater_30];
    }

    /**
     * Generic way to consult age range.
     * 
     * @param  string  $age_start
     * @param  string  $age_end
     * @return  Illuminate\Support\Facades\DB
     */
    public function customQueryAgeRange($age_start, $age_end, $not_between = false){
        if ($not_between){
            return DB::table('enrolled_courses')
                ->join('enrollments', 'enrolled_courses.enrollment_id', '=', 'enrollments.id')
                ->join('courses', 'enrolled_courses.course_id', '=', 'courses.id')
                ->join('students', 'enrollments.student_id', '=', 'students.id')
                ->whereNotBetween('students.birth_at', [DB::raw($age_end), DB::raw($age_start)])
                ->groupBy('courses.title', 'students.gender')
                ->select('courses.title', 'students.gender', DB::raw('COUNT(students.gender) as total'))
                ->get();
        } else {
            return DB::table('enrolled_courses')
                ->join('enrollments', 'enrolled_courses.enrollment_id', '=', 'enrollments.id')
                ->join('courses', 'enrolled_courses.course_id', '=', 'courses.id')
                ->join('students', 'enrollments.student_id', '=', 'students.id')
                ->whereBetween('students.birth_at', [DB::raw($age_end), DB::raw($age_start)])
                ->groupBy('courses.title', 'students.gender')
                ->select('courses.title', 'students.gender', DB::raw('COUNT(students.gender) as total'))
                ->get();
        }
    }
}
