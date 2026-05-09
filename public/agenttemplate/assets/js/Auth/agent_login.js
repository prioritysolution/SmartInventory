function proc_agent_login() {

    const orgCode   = $('#org_code').val().trim();
    const agentCode = $('#agent_code').val().trim();
    const agentPass = $('#agent_pass').val().trim();

    if (!orgCode)   { Swal.fire('Error', 'Please enter Organization Code', 'error'); $('#org_code').focus(); return; }
    if (!agentCode) { Swal.fire('Error', 'Please enter Agent Code', 'error'); $('#agent_code').focus(); return; }
    if (!agentPass) { Swal.fire('Error', 'Please enter Password', 'error'); $('#agent_pass').focus(); return; }

    const loginBtn   = $('button[onclick="proc_agent_login()"]');
    const originalText = loginBtn.text();
    loginBtn.prop('disabled', true).text('Signing In...');

    $.ajax({
        url: baseUrl + '/agent/login',
        type: 'POST',
        data: {
            _token:     $('meta[name="csrf-token"]').attr('content'),
            org_code:   orgCode,
            agent_code: agentCode,
            password:   agentPass
        },
        success: function(response) {
            if (response.success) {
                window.location.href = baseUrl + '/agent/dashboard';
            } else {
                Swal.fire('Error', response.message, 'error');
                loginBtn.prop('disabled', false).text(originalText);
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Login failed. Please try again.';
            Swal.fire('Error', msg, 'error');
            loginBtn.prop('disabled', false).text(originalText);
        }
    });
}

$(document).ready(function () {
    $('#org_code, #agent_code, #agent_pass').on('keypress', function (e) {
        if (e.which === 13) proc_agent_login();
    });
    $('#org_code').focus();
});
