jQuery(document).ready(function($) {
    
    // --- Smooth Dropdown Switcher (For Menus/Caps Tabs) ---
    $('.wrmm-role-selector').not('#wrmm-user-role-select, #wrmm-user-select').on('change', function() {
        let selectedRole = $(this).val();
        $('.wrmm-role-wrapper').removeClass('active').hide();
        $('#wrmm-wrapper-' + selectedRole).fadeIn(300).addClass('active');
    });
    if ($('.wrmm-role-selector').not('#wrmm-user-role-select, #wrmm-user-select').length) {
        $('.wrmm-role-selector').not('#wrmm-user-role-select, #wrmm-user-select').trigger('change');
    }

    // --- User Exceptions: Multi-Level Dropdown Flow ---
    
    $('#wrmm-user-role-select').on('change', function() {
        let role = $(this).val();
        let container = $('#wrmm-user-select-container');
        let userSelect = $('#wrmm-user-select');
        let searchInput = $('#wrmm-user-search-input');
        
        $('#wrmm-user-form-container').hide(); 

        if (!role) {
            container.hide();
            return;
        }

        searchInput.val(''); 
        userSelect.html('<option value="">Loading users...</option>');
        container.css('display', 'flex'); 

        fetchUsers(role, '');
    });

    let userSearchTimer;
    $('#wrmm-user-search-input').on('keyup', function() {
        clearTimeout(userSearchTimer);
        let search = $(this).val();
        let role = $('#wrmm-user-role-select').val();
        
        userSearchTimer = setTimeout(function() {
            $('#wrmm-user-select').html('<option value="">Searching...</option>');
            fetchUsers(role, search);
        }, 400); 
    });

    function fetchUsers(role, search) {
        $.ajax({
            url: wrmm_obj.ajax_url,
            type: 'POST',
            data: { action: 'wrmm_fetch_users', nonce: wrmm_obj.nonce, role: role, search: search },
            success: function(response) {
                let userSelect = $('#wrmm-user-select');
                userSelect.empty();
                
                if (response.success && response.data.length > 0) {
                    userSelect.append('<option value="">2. Select User</option>');
                    response.data.forEach(function(u) {
                        userSelect.append('<option value="'+u.id+'">'+u.name+' ('+u.email+')</option>');
                    });
                } else {
                    userSelect.append('<option value="">No users found</option>');
                }
            }
        });
    }

    $('#wrmm-load-user-btn').on('click', function() {
        let userId = $('#wrmm-user-select').val();
        if (!userId) {
            showToast("Please select a user first.", true);
            return;
        }
        loadUserData(userId);
    });

    // --- Core User Loading Logic ---
    function loadUserData(userId) {
        let btn = $('#wrmm-load-user-btn');
        let container = $('#wrmm-user-form-container');
        btn.text('Loading...').prop('disabled', true);

        $.ajax({
            url: wrmm_obj.ajax_url,
            type: 'POST',
            data: { action: 'wrmm_load_user_data', nonce: wrmm_obj.nonce, user_id: userId },
            success: function(response) {
                btn.text('Load User').prop('disabled', false);
                if (response.success) {
                    $('#wrmm-active-user-name').text(response.data.user_name);
                    $('#wrmm-active-user-id').val(userId);
                    
                    $('.wrmm-exception-select, .wrmm-user-cap-select').val('inherit');
                    
                    // Populate Menus
                    if (response.data.exceptions.show) {
                        response.data.exceptions.show.forEach(function(slug) {
                            let safeSlug = slug.replace(/\|/g, '\\|');
                            $('.wrmm-exception-select[data-slug="' + safeSlug + '"]').val('show');
                        });
                    }
                    if (response.data.exceptions.hide) {
                        response.data.exceptions.hide.forEach(function(slug) {
                            let safeSlug = slug.replace(/\|/g, '\\|');
                            $('.wrmm-exception-select[data-slug="' + safeSlug + '"]').val('hide');
                        });
                    }

                    // Populate Capabilities
                    if (response.data.user_caps) {
                        $.each(response.data.user_caps, function(cap, value) {
                            if (value === true) {
                                $('.wrmm-user-cap-select[data-cap="'+cap+'"]').val('grant');
                            } else if (value === false) {
                                $('.wrmm-user-cap-select[data-cap="'+cap+'"]').val('revoke');
                            }
                        });
                    }

                    // Dynamically hide menus user does not have capability for
                    $('.wrmm-menu-list .wrmm-list-item').each(function() {
                        let reqCap = $(this).data('req-cap');
                        if (reqCap && !response.data.all_caps[reqCap]) {
                            $(this).hide();
                        } else {
                            $(this).show();
                            $(this).find('.wrmm-sublist-item').each(function() {
                                let reqSubCap = $(this).data('req-cap');
                                if (reqSubCap && !response.data.all_caps[reqSubCap]) {
                                    $(this).hide();
                                } else {
                                    $(this).show();
                                }
                            });
                        }
                    });
                    
                    container.fadeIn();
                } else {
                    container.hide();
                    showToast(response.data, true);
                }
            },
            error: function() {
                btn.text('Load User').prop('disabled', false);
                showToast("Server error.", true);
            }
        });
    }

    // --- Select All Toggles ---
    $(document).on('change', '.wrmm-menu-select-all', function() {
        let isChecked = $(this).is(':checked');
        $(this).closest('.wrmm-list-item').find('.wrmm-sublist .wrmm-cb:not(:disabled)').prop('checked', isChecked);
    });
    $(document).on('change', '.wrmm-cap-select-all', function() {
        let isChecked = $(this).is(':checked');
        $(this).closest('.wrmm-cap-group').find('.wrmm-cb:not(:disabled)').prop('checked', isChecked);
    });

    // --- Floating Toast Notification Function ---
    function showToast(message, isError = false) {
        let toast = $('#wrmm-toast');
        toast.text(message);
        toast.removeClass('wrmm-toast-error wrmm-toast-success show');
        isError ? toast.addClass('wrmm-toast-error') : toast.addClass('wrmm-toast-success');
        toast.addClass('show');
        setTimeout(function(){ toast.removeClass('show'); }, 3500);
    }

    // --- Unified AJAX Form Submitter ---
    $('.wrmm-form').on('submit', function(e) {
        e.preventDefault();
        
        let form = $(this);
        let btn = form.find('.wrmm-submit-btn');
        let role = form.data('role');
        let action = form.data('action');
        let userId = form.find('#wrmm-active-user-id').val();
        
        let submissionData = null;

        if ( action === 'wrmm_save_settings' ) {
            submissionData = [];
            form.find('input.wrmm-cb:not(:checked):not(:disabled)').each(function() {
                submissionData.push($(this).val());
            });
        } else if ( action === 'wrmm_save_capabilities' || action === 'wrmm_save_advanced' ) {
            submissionData = [];
            form.find('input.wrmm-cb:checked').each(function() {
                submissionData.push($(this).val());
            });
        } else if ( action === 'wrmm_save_user_exceptions' ) {
            let showArr = [];
            let hideArr = [];
            form.find('.wrmm-exception-select').each(function() {
                let val = $(this).val();
                let slug = $(this).data('slug');
                if (val === 'show') showArr.push(slug);
                if (val === 'hide') hideArr.push(slug);
            });
            
            let capsObj = {};
            form.find('.wrmm-user-cap-select').each(function() {
                let val = $(this).val();
                let cap = $(this).data('cap');
                if (val !== 'inherit') capsObj[cap] = val;
            });

            submissionData = { show: showArr, hide: hideArr, caps: capsObj };
        }

        let redirectData = "";
        if( action === 'wrmm_save_advanced' ) {
            redirectData = form.find('input[name="login_redirect"]').val();
        }

        btn.addClass('wrmm-loading').prop('disabled', true);

        $.ajax({
            url: wrmm_obj.ajax_url,
            type: 'POST',
            data: { action: action, nonce: wrmm_obj.nonce, role: role, user_id: userId, data: submissionData, redirect: redirectData },
            success: function(response) {
                btn.removeClass('wrmm-loading').prop('disabled', false);
                if (response.success) {
                    showToast(response.data);
                    // Automatically reload to show newly available menus if a capability was granted!
                    if (action === 'wrmm_save_user_exceptions') loadUserData(userId);
                } else {
                    showToast("Error: " + response.data, true);
                }
            },
            error: function() {
                btn.removeClass('wrmm-loading').prop('disabled', false);
                showToast("A server error occurred.", true);
            }
        });
    });
});