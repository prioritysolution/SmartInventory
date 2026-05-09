"use strict";
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
//FOR VIEW PASSWORD
$(document).on('click', '.toggle-password', function () {
    var $input = $('.pass-inputs');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $(this).removeClass('isax-eye-slash').addClass('isax-eye');
    } else {
        $input.attr('type', 'password');
        $(this).removeClass('isax-eye').addClass('isax-eye-slash');
    }
});

function proc_login() {
    var pOrg_Code = $("#org_code").val();
    var pUser_Name = $("#auth_user").val();
    var pUser_Pass = $("#auth_pass").val();

    if (pOrg_Code == "") {
        Swal.fire({icon: 'warning', title: 'Warning', text: 'Please Enter Organization Code !!'});
    } else if (pUser_Name == "") {
        Swal.fire({icon: 'warning', title: 'Warning', text: 'Please Enter User Name !!'});
    } else if (pUser_Pass == "") {
        Swal.fire({icon: 'warning', title: 'Warning', text: 'Please Enter User Password !!'});
    } else {
        $.ajax({
            url: baseUrl + "/User/Login",
            type: "POST",
            data: {
                pOrg_Code: pOrg_Code,
                pUser_Name: pUser_Name,
                pUser_Pass: pUser_Pass
            },
            dataType: "json",
            // beforeSend: function () {
            //     Swal.fire({title: 'Please wait...', allowOutsideClick: false, didOpen: () => {Swal.showLoading()}});
            // },
            success: function (response) {
                Swal.close();
                if (response.status === "success") {
                    window.location.href = baseUrl + "/Dashboard";
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: response.data || 'Something went wrong!'});
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = Object.values(errors).flat().join('\n');
                    Swal.fire({icon: 'error', title: 'Validation Error', text: errorMsg});
                } else {
                    let errorMessage = xhr.responseJSON?.message || error || "Unknown error";
                    Swal.fire({icon: 'error', title: 'Error', text: errorMessage});
                }
            }
        });
    }
}
