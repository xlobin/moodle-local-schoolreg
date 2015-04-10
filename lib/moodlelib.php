<?php
function getMyQuiz($quiz) {
    global $DB;

    $quizSlots = $DB->get_records('quiz_slots', array('quizid' => $quiz->id));
    foreach ($quizSlots as $keySlot => $slot) {
        $questions = $DB->get_records('question', array('id' => $slot->questionid));
        foreach ($questions as $keyQuestion => $question) {
            /* ANSWER */
            $answer = $DB->get_records('question_answers', array('question' => $question->id));
            if (count($answer) > 0) {
                $questions[$keyQuestion]->my_item['question_answers'] = $answer;
            }

            /* HINTS */
            $hint = $DB->get_records('question_hints', array('questionid' => $question->id));
            if (count($hint) > 0) {
                $questions[$keyQuestion]->my_item['question_hints'] = $hint;
            }

            /* TRUEFALSE */
            $truefalse = $DB->get_records('question_truefalse', array('question' => $question->id));
            if (count($truefalse) > 0) {
                $questions[$keyQuestion]->my_item['question_truefalse'] = $truefalse;
            }

            /* NUMERICAL */
            $numerical = $DB->get_records('question_numerical', array('question' => $question->id));
            if (count($numerical) > 0) {
                $questions[$keyQuestion]->my_item['question_numerical'] = $numerical;

                $numerical_option = $DB->get_records('question_numerical_options', array('question' => $question->id));
                if (count($numerical_option) > 0) {
                    $questions[$keyQuestion]->my_item['question_numerical_options'] = $numerical_option;
                }
                $numerical_unit = $DB->get_records('question_numerical_units', array('question' => $question->id));
                if (count($numerical_unit) > 0) {
                    $questions[$keyQuestion]->my_item['question_numerical_units'] = $numerical_unit;
                }
            }

            /* CALCULATED OPTION */
            $calculated_option = $DB->get_records('question_calculated_options', array('question' => $question->id));
            if (count($calculated_option) > 0) {
                $questions[$keyQuestion]->my_item['question_calculated_options'] = $calculated_option;
            }

            /* CALCULATED */
            $calculated = $DB->get_records('question_calculated', array('question' => $question->id));
            if (count($calculated) > 0) {
                $questions[$keyQuestion]->my_item['question_calculated'] = $calculated;
            }

            /* MULTICHOICE OPTION */
            $multiopt = $DB->get_records('qtype_multichoice_options', array('questionid' => $question->id));
            if (count($multiopt) > 0) {
                $questions[$keyQuestion]->my_item['qtype_multichoice_options'] = $multiopt;
            }

            /* MULTIANSWER */
            $multianswer = $DB->get_records('question_multianswer', array('question' => $question->id));
            if (count($multianswer) > 0) {
                $questions[$keyQuestion]->my_item['question_multianswer'] = $multianswer;
            }

            /* RANDOMSAMATCH OPTION */
            $randomsamatch = $DB->get_records('qtype_randomsamatch_options', array('questionid' => $question->id));
            if (count($randomsamatch) > 0) {
                $questions[$keyQuestion]->my_item['qtype_randomsamatch_options'] = $randomsamatch;
            }

            /* SHORTANSWER OPTION */
            $shortopt = $DB->get_records('qtype_shortanswer_options', array('questionid' => $question->id));
            if (count($shortopt) > 0) {
                $questions[$keyQuestion]->my_item['qtype_shortanswer_options'] = $shortopt;
            }

            /* ESSAY */
            $essay = $DB->get_records('qtype_essay_options', array('questionid' => $question->id));
            if (count($essay) > 0) {
                $questions[$keyQuestion]->my_item['qtype_essay_options'] = $essay;
            }

            /* MATCH SUBQUESTION */
            $matchsub = $DB->get_records('qtype_match_subquestions', array('questionid' => $question->id));
            if (count($matchsub) > 0) {
                $questions[$keyQuestion]->my_item['qtype_match_subquestions'] = $matchsub;
            }

            /* MATCH OPTION */
            $matchopt = $DB->get_records('qtype_match_options', array('questionid' => $question->id));
            if (count($matchopt) > 0) {
                $questions[$keyQuestion]->my_item['qtype_match_options'] = $matchopt;
            }

            /* DATASET */
            $dataset = $DB->get_records('question_datasets', array('question' => $question->id));
            if (count($dataset) > 0) {
                $questions[$keyQuestion]->my_item['question_datasets'] = $dataset;
            }
        }
        $quizSlots[$keySlot]->my_item['question'] = $questions;
    }

    $quizFeedback = $DB->get_records('quiz_feedback', array('quizid' => $quiz->id));

    return (($quizSlots) ? array('quiz_slots' => $quizSlots, 'quiz_feedback' => $quizFeedback) : null);
}

function getMyBook($book) {
    global $DB;

    $chapter = $DB->get_records('book_chapters', array('bookid' => $book->id));
    return (($chapter) ? array('book_chapters' => $chapter) : null);
}

function getMyChoice($choice) {
    global $DB;

    $choices = $DB->get_records('choice_options', array('choiceid' => $choice->id));
    return (($choices) ? array('choice_options' => $choices) : null);
}

function getMyGlossary($glo) {
    global $DB;

    $glossary = array();
    // categories //
    $categories = $DB->get_records('glossary_categories', array('glossaryid' => $glo->id));
    if (count($categories) > 0) {
        $glossary['glossary_categories'] = $categories;
    }

    // entries //
    $entries = $DB->get_records('glossary_entries', array('glossaryid' => $glo->id));
    if (count($entries) > 0) {
        $glossary['glossary_entries'] = $entries;
    }

    // alias //
    $alias = $DB->get_records_sql("SELECT * FROM {glossary_alias} WHERE entryid IN (SELECT id FROM {glossary_entries} WHERE glossaryid = ? )", array($glo->id));
    if (count($alias) > 0) {
        $glossary['glossary_alias'] = $alias;
    }

    // entries_categories //
    $entries_categories = $DB->get_records_sql("SELECT * FROM {glossary_entries_categories} WHERE categoryid IN (SELECT id FROM {glossary_categories} WHERE glossaryid = ?)", array($glo->id));
    if (count($entries_categories) > 0) {
        $glossary['glossary_entries_categories'] = $entries_categories;
    }

    return $glossary;
}

function getMyLesson($lesson) {
    global $DB;

    $lessons = array();
    $pages = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id));
    $lessons['lesson_pages'] = $pages;

    $answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id));
    $lessons['lesson_answers'] = $answers;

    return (($lessons) ? $lessons : null);
}

function getMyWiki($wikis) {
    global $DB;

    $subwikis = $DB->get_records('wiki_subwikis', array('wikiid' => $wikis->id));
    if (count($subwikis) > 0) {
        foreach ($subwikis as $keySubwikis => $sub) {
            $pages = $DB->get_records('wiki_pages', array('subwikiid' => $sub->id));
            if (count($pages) > 0) {
                foreach ($pages as $keyPages => $page) {
                    $versions = $DB->get_records('wiki_versions', array('pageid' => $page->id));
                    if (count($versions) > 0) {
                        $pages[$keyPages]->my_item['wiki_versions'] = $versions;
                    }
                }
                $subwikis[$keySubwikis]->my_item['wiki_pages'] = $pages;
            }

            $links = $DB->get_records('wiki_links', array('subwikiid' => $sub->id));
            if (count($links) > 0) {
                $subwikis[$keySubwikis]->my_item['wiki_links'] = $links;
            }
        }
    }

    return (($subwikis) ? array('wiki_subwikis' => $subwikis) : null);
}

function getMyForum($forum) {
    global $DB;

    $discussions = $DB->get_records('forum_discussions', array('forum' => $forum->id, 'userid' => 2));
    if (count($discussions) > 0) {
        foreach ($discussions as $keyDiscussions => $value) {
            $posts = $DB->get_records('forum_posts', array('discussion' => $value->id, 'userid' => 2));
            if (count($posts) > 0) {
                $discussions[$keyDiscussions]->my_item['forum_posts'] = $posts;
            }
        }
    }

    return (($discussions) ? array('forum_discussions' => $discussions) : null);
}

function getMyWorkshop($workshop) {
    global $DB;

    $ws = array();
    $workshop_old = $DB->get_records('workshop_old', array('course' => $workshop->course));
    $elements_old = $DB->get_records('workshop_elements_old', array('workshopid' => $workshop->id));
    $grades_old = $DB->get_records('workshop_grades_old', array('workshopid' => $workshop->id));
    $rubrics_old = $DB->get_records('workshop_rubrics_old', array('workshopid' => $workshop->id));
    $stockcomments_old = $DB->get_records('workshop_stockcomments_old', array('workshopid' => $workshop->id));
    $submissions = $DB->get_records('workshop_submissions', array('workshopid' => $workshop->id));
    if (count($submissions) > 0) {
        foreach ($submissions as $keySubmission => $sub) {
            $assessments = $DB->get_records('workshop_assessments', array('submissionid' => $sub->id));
            if (count($assessments) > 0) {
                foreach ($assessments as $keyAssessment => $asses) {
                    $grades = $DB->get_records('workshop_grades', array('assessmentid' => $asses->id));
                    $assessments[$keyAssessment]->my_item['workshop_grades'] = ($grades) ? $grades : null;
                }
            }
            $submissions[$keySubmission]->my_item['workshop_assessments'] = ($assessments) ? $assessments : null;
        }
    }

    $workshopallocation_scheduled = $DB->get_records('workshopallocation_scheduled', array('workshopid' => $workshop->id));
    $workshopeval_best_settings = $DB->get_records('workshopeval_best_settings', array('workshopid' => $workshop->id));
    $workshopform_accumulative = $DB->get_records('workshopform_accumulative', array('workshopid' => $workshop->id));
    $workshopform_comments = $DB->get_records('workshopform_comments', array('workshopid' => $workshop->id));
    $workshopform_numerrors = $DB->get_records('workshopform_numerrors', array('workshopid' => $workshop->id));
    $workshopform_numerrors_map = $DB->get_records('workshopform_numerrors_map', array('workshopid' => $workshop->id));
    $workshopform_rubric = $DB->get_records('workshopform_rubric', array('workshopid' => $workshop->id));
    if (count($workshopform_rubric) > 0) {
        foreach ($workshopform_rubric as $keyWorkshopRubric => $wr) {
            $workshopform_rubric_levels = $DB->get_records('workshopform_rubric_levels', array('dimensionid' => $wr->id));
            if (count($workshopform_rubric_levels) > 0) {
                $workshopform_rubric[$keyWorkshopRubric]->my_item['workshopform_rubric_levels'] = $workshopform_rubric_levels;
            }
        }
    }

    $workshopform_rubric_config = $DB->get_records('workshopform_rubric_config', array('workshopid' => $workshop->id));

    $ws['workshop_old'] = $workshop_old;
    $ws['workshop_elements_old'] = $elements_old;
    $ws['workshop_grades_old'] = $grades_old;
    $ws['workshop_rubrics_old'] = $rubrics_old;
    $ws['workshop_stockcomments_old'] = $stockcomments_old;
    $ws['workshopallocation_scheduled'] = $workshopallocation_scheduled;
    $ws['workshopeval_best_settings'] = $workshopeval_best_settings;
    $ws['workshopform_accumulative'] = $workshopform_accumulative;
    $ws['workshopform_comments'] = $workshopform_comments;
    $ws['workshopform_numerrors'] = $workshopform_numerrors;
    $ws['workshopform_numerrors_map'] = $workshopform_numerrors_map;
    $ws['workshopform_rubric'] = $workshopform_rubric;
    $ws['workshopform_rubric_config'] = $workshopform_rubric_config;

    return (($ws) ? $ws : null);
}

function getMyFeedback($feedback) {
    global $DB;

    $feedback_data = array();

    $item = $DB->get_records('feedback_item', array('feedback' => $feedback->id));
    $feedback_data['feedback_item'] = ($item) ? $item : null;

    $sitecourse_map = $DB->get_records('feedback_sitecourse_map', array('feedbackid' => $feedback->id, 'courseid' => $feedback->course));
    $feedback_data['feedback_sitecourse_map'] = ($sitecourse_map) ? $sitecourse_map : null;

    $template = $DB->get_records('feedback_template', array('course' => $feedback->course));
    $feedback_data['feedback_template'] = ($template) ? $template : null;

    $value = $DB->get_records('feedback_value', array('course_id' => $feedback->course));
    $feedback_data['feedback_value'] = ($value) ? $value : null;

    $valuetmp = $DB->get_records('feedback_valuetmp', array('course_id' => $feedback->course));
    $feedback_data['feedback_valuetmp'] = ($valuetmp) ? $valuetmp : null;

    return (($feedback_data) ? $feedback_data : null);
}

function getMyScorm($scorm) {
    global $DB;

    $scoes = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id));
    if (count($scoes) > 0) {
        foreach ($scoes as $keyScorm => $sco) {

            // SCOES_DATA //
            $scoes_data = $DB->get_records('scorm_scoes_data', array('scoid' => $sco->id));
            if (count($scoes_data) > 0) {
                $scoes[$keyScorm]->my_item['scorm_scoes_data'] = $scoes_data;
            }

            // SEQ_MAPINFO //
            $seq_mapinfo = $DB->get_records('scorm_seq_mapinfo', array('scoid' => $sco->id));
            if (count($seq_mapinfo) > 0) {
                $scoes[$keyScorm]->my_item['scorm_seq_mapinfo'] = $seq_mapinfo;
            }

            // SEQ_OBJECTIVE //
            $seq_objective = $DB->get_records('scorm_seq_objective', array('scoid' => $sco->id));
            if (count($seq_objective) > 0) {
                $scoes[$keyScorm]->my_item['scorm_seq_objective'] = $seq_objective;
            }

            // SEQ_ROLLUPRULE //
            $seq_rolluprole = $DB->get_records('scorm_seq_rolluprule', array('scoid' => $sco->id));
            if (count($seq_rolluprole) > 0) {
                $scoes[$keyScorm]->my_item['scorm_seq_rolluprule'] = $seq_rolluprole;
            }

            // SEQ_ROLLUPRULECOND //
            $seq_rolluprolecond = $DB->get_records('scorm_seq_rolluprulecond', array('scoid' => $sco->id));
            if (count($seq_rolluprolecond) > 0) {
                $scoes[$keyScorm]->my_item['scorm_seq_rolluprulecond'] = $seq_rolluprolecond;
            }

            // SEQ_RULECOND //
            $seq_rulecond = $DB->get_records('scorm_seq_rulecond', array('scoid' => $sco->id));
            if (count($seq_rulecond) > 0) {
                $scoes[$keyScorm]->my_item['scorm_seq_rulecond'] = $seq_rulecond;
            }

            // SEQ_RULECONDS //
            $seq_ruleconds = $DB->get_records('scorm_seq_ruleconds', array('scoid' => $sco->id));
            if (count($seq_ruleconds) > 0) {
                $scoes[$keyScorm]->my_item['scorm_seq_ruleconds'] = $seq_ruleconds;
            }
        }
    }

    return (($scoes) ? array('scorm_scoes' => $scoes) : null);
}
