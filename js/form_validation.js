var valid = true;

// Verification du formulaire
$('input, select').focusout(function() {
    validate($(this));
});

function validate(input) {
    if(is_valid(input)) {
        input.parent('div').removeClass('has-error');
        input.parent('div').addClass('has-success');
        return true;
    }
    else {
        input.parent('div').addClass('has-error');
        return false;
    }
}

// On met la validation avant la submition du formulaire
$('button[type=submit]').on('click',function(e) {
    valid = true;
    $('input, select').each(function() {
        valid = valid? validate($(this)):valid;
    });
    if(!valid) e.preventDefault();
    return valid;
});