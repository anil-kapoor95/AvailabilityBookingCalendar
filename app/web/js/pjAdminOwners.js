var jQuery_1_8_2 = jQuery_1_8_2 || jQuery.noConflict();
(function ($, undefined) {
	$(function () {
		var validate = ($.fn.validate !== undefined),
			spinner = ($.fn.spinner !== undefined),
			multilang = ($.fn.multilang !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			chosen = ($.fn.chosen !== undefined),
			$frmCreateOwner = $("#frmCreateOwner"),
			$frmUpdateOwner = $("#frmUpdateOwner"),
			$document = $(document);
		
		if (chosen) {
			$(".select-item").chosen();
		}
		
		if ($frmCreateOwner.length && validate) {
			$frmCreateOwner.validate({
				rules: {
					"email": {
						remote: "index.php?controller=pjAdminOwners&action=pjActionCheckEmail"
					}
				},
				onkeyup: false,
				submitHandler: function (form) {
					var ladda_buttons = $(form).find('.ladda-button');
				    if(ladda_buttons.length > 0)
                    {
                        var l = ladda_buttons.ladda();
                        l.ladda('start');
                    }
					$.post('index.php?controller=pjBaseUsers&action=pjActionCheckPassword', $(form).serialize()).done(function (data) {
            			if(data.status == 'OK')
        				{
            				form.submit();
        				}else{
        					l.ladda('stop');
        					swal({
        		                title: myLabel.invalid_password_title,
        		                type: "warning",
        		                text: data.text,
        		                showCancelButton: false,
        		                confirmButtonColor: "#11511a",
        		                confirmButtonText: myLabel.btn_ok,
        		                closeOnConfirm: true,
        		            });
        				}
            		});
					return false;
				}
			});
		}
		
		if ($frmUpdateOwner.length && validate) {
			$frmUpdateOwner.validate({
				rules: {
					"email": {
						remote: "index.php?controller=pjAdminOwners&action=pjActionCheckEmail&id=" + $frmUpdateOwner.find("input[name='id']").val()
					}
				},
				onkeyup: false,
				submitHandler: function (form) {
					var ladda_buttons = $(form).find('.ladda-button');
				    if(ladda_buttons.length > 0)
                    {
                        var l = ladda_buttons.ladda();
                        l.ladda('start');
                    }
					var pswd = $(form).find("#password").val();
					if (!pswd.length) {
						return true;
					}
					$.post('index.php?controller=pjBaseUsers&action=pjActionCheckPassword', $(form).serialize()).done(function (data) {
            			if(data.status == 'OK')
        				{
            				form.submit();
        				}else{
        					l.ladda('stop');
        					swal({
        		                title: myLabel.invalid_password_title,
        		                type: "warning",
        		                text: data.text,
        		                showCancelButton: false,
        		                confirmButtonColor: "#11511a",
        		                confirmButtonText: myLabel.btn_ok,
        		                closeOnConfirm: true,
        		            });
        				}
            		});
					return false;
				}
			});
		}
		if ($("#grid").length > 0 && datagrid) 
		{
			function formatEmail(str) {
				return ['<a href="mailto:', str, '">', str, '</a>'].join("");
			}
			
			var editable = false;
			var buttons = [];
			
			if (pjGrid.has_update) {
				editable = true;
				buttons.push({type: "edit", url: "index.php?controller=pjAdminOwners&action=pjActionUpdate&id={:id}"});
			}
			if (pjGrid.has_delete) {
				buttons.push({type: "delete", url: "index.php?controller=pjAdminOwners&action=pjActionDeleteOwner&id={:id}"});
			}
		
			var actions = [];
			if (pjGrid.has_delete_bulk) {
				actions.push({text: myLabel.delete_selected, url: "index.php?controller=pjAdminOwners&action=pjActionDeleteOwnerBulk", render: true, confirmation: myLabel.delete_confirmation});
			}
			if (pjGrid.has_update) {
				actions.push({text: myLabel.revert_status, url: "index.php?controller=pjAdminOwners&action=pjActionStatusOwner", render: true});
			}
			
			var select = false;
			if (actions.length) {
				select = {
					field: "id",
					name: "record[]",
					cellClass: 'cell-width-2'
				};
			}

			var $grid = $("#grid").datagrid({
				buttons: buttons,
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: editable},
				          {text: myLabel.email, type: "text", sortable: true, editable: editable, renderer: formatEmail},
				          {text: myLabel.last_login, type: "date", sortable: true, editable: false, renderer: $.datagrid._formatDate, dateFormat: pjGrid.jsDateFormat},
				          {text: myLabel.status, type: "toggle", sortable: true, editable: editable, positiveLabel: myLabel.active, positiveValue: "T", negativeLabel: myLabel.inactive, negativeValue: "F"}],
				dataUrl: "index.php?controller=pjAdminOwners&action=pjActionGetOwner",
				dataType: "json",
				fields: ['name', 'email', 'last_login', 'status'],
				paginator: {
					actions: actions,
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminOwners&action=pjActionSaveOwner&id={:id}",
				select: select
			});
		}
		
		$(document).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("btn-primary active").removeClass("btn-default")
				.siblings(".btn").removeClass("btn-primary active").addClass("btn-default");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				status: "",
				q: "",
				page: 1
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", $grid.data('datagrid').settings.dataUrl, content.column, content.direction, content.page, content.rowCount);
			
		}).on("click", ".btn-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache"),
				obj = {};
			$this.addClass("btn-primary active").removeClass("btn-default")
				.siblings(".btn").removeClass("btn-primary active").addClass("btn-default");
			obj.status = "";
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$.extend(cache, {
				page: 1
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", $grid.data('datagrid').settings.dataUrl, content.column, content.direction, content.page, content.rowCount);
			
		}).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val(),
				page: 1
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", $grid.data('datagrid').settings.dataUrl, content.column, content.direction, 1, content.rowCount);
			return false;
		});
	});
})(jQuery_1_8_2);