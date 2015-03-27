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

    return (($quizSlots) ? array('quiz_slots' => $quizSlots) : null);
}

function getMyBook($book) {
    global $DB;

    $chapter = $DB->get_records('book_chapters', array('bookid' => $book->id));
    return (($chapter) ? array('book_chapters' => $chapter) : null);
}

function getMyChoice($choice){
    global $DB;

    $choices = $DB->get_records('choice_options', array('choiceid' => $choice->id));
    return (($choices) ? array('choice_options' => $choices) : null);
}

function getMyGlossary($glo){
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
        $glossary['alias'] = $alias;
    }

    // entries_categories //
    $entries_categories = $DB->get_records_sql("SELECT * FROM {glossary_entries_categories} WHERE categoryid IN (SELECT id FROM {glossary_categories} WHERE glossaryid = ?)", array($glo->id));
    if (count($entries_categories) > 0) {
        $glossary['glossary_entries_categories'] = $entries_categories;
    }

    return $glossary;
}

function getMyLesson($lesson){
    global $DB;

    $lessons = array();
    $pages = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id));
    $lessons['lesson_pages'] = $pages;

    $answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id));
    $lessons['lesson_answers'] = $answers;

    return (($lessons) ? $lessons : null);
}

function getMyWiki($wikis){
    global $DB;

    $subwikis = $DB->get_records('wiki_subwikis', array('wikiid' => $wikis->id));
    if(count($subwikis)>0){
        foreach($subwikis as $keySubwikis => $sub){
            $pages = $DB->get_records('wiki_pages', array('subwikiid' => $sub->id));
            if(count($pages)>0){
                foreach($pages as $keyPages => $page){
                    $versions = $DB->get_records('wiki_versions', array('pageid' => $page->id));
                    if(count($versions)>0){
                        $pages[$keyPages]->my_item['wiki_versions'] = $versions;
                    }
                }
                $subwikis[$keySubwikis]->my_item['wiki_pages'] = $pages;
            }

            $links = $DB->get_records('wiki_links', array('subwikiid' => $sub->id));
            if(count($links)>0){
                $subwikis[$keySubwikis]->my_item['wiki_links'] = $links;
            }
        }
    }

    return (($subwikis) ? array('wiki_subwikis' => $subwikis) : null);
}