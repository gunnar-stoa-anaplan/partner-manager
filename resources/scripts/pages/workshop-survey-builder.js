document.addEventListener('DOMContentLoaded', function () {
    var list = document.getElementById('questionList');
    var addBtn = document.getElementById('addQuestionBtn');
    var form = document.getElementById('templateForm');
    var hiddenInput = document.getElementById('questionsJsonInput');

    if (!list || !form) return;

    var questions = window.workshopSurveyQuestions || [];
    var types = window.workshopSurveyQuestionTypes || {};

    function randomId() {
        return 'q' + Math.random().toString(36).slice(2, 8);
    }

    function optionsToText(options) {
        return Array.isArray(options) ? options.join('\n') : '';
    }

    function correctToText(correct) {
        if (Array.isArray(correct)) return correct.join('\n');
        return correct || '';
    }

    function buildRow(question, index) {
        var row = document.createElement('div');
        row.className = 'card question-card mb-3';
        row.dataset.questionId = question.id || randomId();

        var typeOptions = Object.keys(types).map(function (key) {
            var selected = (question.type || 'single_select') === key ? ' selected' : '';
            return '<option value="' + key + '"' + selected + '>' + types[key] + '</option>';
        }).join('');

        row.innerHTML =
            '<div class="card-body">' +
            '<div class="d-flex gap-2 align-items-start mb-2">' +
            '<div class="text-muted fw-medium pt-2" style="min-width: 2.5rem;">Q' + (index + 1) + '</div>' +
            '<select class="form-select form-select-sm question-type" style="max-width: 180px;">' + typeOptions + '</select>' +
            '<input type="text" class="form-control form-control-sm question-text" placeholder="Question text" value="' + escapeAttr(question.text || '') + '">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary move-up" title="Move up">↑</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary move-down" title="Move down">↓</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-question">Remove</button>' +
            '</div>' +
            '<div class="likert-note mb-2" style="display:none;">Fixed 5-point scale: Very comfortable → Very uncomfortable.</div>' +
            '<textarea class="form-control form-control-sm mb-2 question-options" rows="3" placeholder="One option per line" style="display:none;">' + escapeHtml(optionsToText(question.options)) + '</textarea>' +
            '<div class="mb-2 correct-answer-box" style="display:none;">' +
            '<label class="form-label fs-sm fw-medium mb-1">Correct answer</label>' +
            '<textarea class="form-control form-control-sm question-correct" rows="2" placeholder="One correct option per line (leave blank if not scored)">' + escapeHtml(correctToText(question.correct)) + '</textarea>' +
            '</div>' +
            '<div class="form-check">' +
            '<input type="checkbox" class="form-check-input question-required" id="required-' + row.dataset.questionId + '"' + (question.required ? ' checked' : '') + '>' +
            '<label class="form-check-label fs-sm" for="required-' + row.dataset.questionId + '">Required</label>' +
            '</div>' +
            '</div>';

        var typeSelect = row.querySelector('.question-type');
        typeSelect.addEventListener('change', function () { updateFieldVisibility(row); });
        row.querySelector('.remove-question').addEventListener('click', function () {
            row.remove();
            renumber();
        });
        row.querySelector('.move-up').addEventListener('click', function () {
            var prev = row.previousElementSibling;
            if (prev) { list.insertBefore(row, prev); renumber(); }
        });
        row.querySelector('.move-down').addEventListener('click', function () {
            var next = row.nextElementSibling;
            if (next) { list.insertBefore(next, row); renumber(); }
        });

        updateFieldVisibility(row);

        return row;
    }

    function updateFieldVisibility(row) {
        var type = row.querySelector('.question-type').value;
        var optionsField = row.querySelector('.question-options');
        var correctBox = row.querySelector('.correct-answer-box');
        var likertNote = row.querySelector('.likert-note');

        var needsOptions = type === 'single_select' || type === 'multi_select';
        optionsField.style.display = needsOptions ? '' : 'none';
        correctBox.style.display = needsOptions ? '' : 'none';
        likertNote.style.display = type === 'likert' ? '' : 'none';
    }

    function renumber() {
        Array.from(list.children).forEach(function (row, index) {
            row.querySelector('.card-body > div:first-child > div:first-child').textContent = 'Q' + (index + 1);
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    }

    questions.forEach(function (question, index) {
        list.appendChild(buildRow(question, index));
    });

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            list.appendChild(buildRow({ type: 'single_select' }, list.children.length));
        });
    }

    form.addEventListener('submit', function () {
        var serialized = Array.from(list.children).map(function (row) {
            var type = row.querySelector('.question-type').value;
            var question = {
                id: row.dataset.questionId,
                text: row.querySelector('.question-text').value,
                type: type,
                required: row.querySelector('.question-required').checked,
            };

            if (type === 'single_select' || type === 'multi_select') {
                question.options = row.querySelector('.question-options').value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
                var correctLines = row.querySelector('.question-correct').value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
                if (correctLines.length === 1) {
                    question.correct = correctLines[0];
                } else if (correctLines.length > 1) {
                    question.correct = correctLines;
                }
            }

            return question;
        });

        hiddenInput.value = JSON.stringify(serialized);
    });
});
