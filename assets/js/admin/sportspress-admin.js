jQuery(document).ready(function($){

	// Tiptip
	$(".tips").tipTip({
		delay: 200,
		fadeIn: 100,
		fadeOut: 100
	});

	// Chosen select
	$(".chosen-select, #poststuff #post_author_override").chosen({
		allow_single_deselect: true,
		single_backstroke_delete: false,
		disable_search_threshold: 10,
		placeholder_text_multiple: localized_strings.none
	});

	// Auto key placeholder
	$("#poststuff #title").on("keyup", function() {
		val = $(this).val().replace(/[^a-z]/gi,"").toLowerCase();
		$("#sp_key").attr("placeholder", val);
		$("#sp_default_key").val(val);
	});

	// Activate auto key placeholder
	$("#poststuff #title").keyup();

	// Table switcher
	$(".sp-table-panel").siblings(".sp-table-bar").find("a").click(function() {
		$(this).closest("li").find("a").addClass("current").closest("li").siblings().find("a").removeClass("current").closest(".sp-table-bar").siblings($(this).attr("href")).show().siblings(".sp-table-panel").hide();
		return false;
	});

	// Tab switcher
	$(".sp-tab-panel").siblings(".sp-tab-bar").find("a").click(function() {
		$(this).closest("li").removeClass("wp-tab").addClass("wp-tab-active").siblings().removeClass("wp-tab-active").addClass("wp-tab").closest(".wp-tab-bar").siblings($(this).attr("href")).show().trigger('checkCheck').siblings(".wp-tab-panel").hide();
		return false;
	});

	// Tab filter
	$(".sp-tab-panel").siblings(".sp-tab-select").find("select").change(function() {
		var val = $(this).val();
		var filter = ".sp-filter-"+val;
		var $filters = $(this).closest(".sp-tab-select").siblings(".sp-tab-select");
		if($filters.length) {
			$filters.each(function() {
				filterval = $(this).find("select").val();
				if(filterval !== undefined)
					filter += ".sp-filter-"+filterval;
			});
		}
		$panel = $(this).closest(".sp-tab-select").siblings(".sp-tab-panel");
		$panel.each(function() {
			$(this).find(".sp-post").hide(0, function() {
				$(this).find("input").prop("disabled", true);
				$(this).filter(filter).show(0, function() {
					$(this).find("input").prop("disabled", false);
				});
			});
			if($(this).find(".sp-post:visible").length > 0) {
				$(this).find(".sp-select-all-container").show();
				$(this).find(".sp-show-all-container").show();
				$(this).find(".sp-not-found-container").hide();
			} else {
				$(this).find(".sp-select-all-container").hide();
				$(this).find(".sp-show-all-container").hide();
				$(this).find(".sp-not-found-container").show();
			}
		});
	});

	// Trigger tab filter
	$(".sp-tab-panel").siblings(".sp-tab-select").find("select").change();

	// Filter show all action links
	$(".sp-tab-panel").find(".sp-post input:checked").each(function() {
		$(this).prop("disabled", false).closest("li").show().siblings(".sp-not-found-container").hide().siblings(".sp-show-all-container").show();
	});

	// Show all filter
	$(".sp-tab-panel").on("click", ".sp-show-all", function() {
		$(this).closest("li").hide().siblings(".sp-post, .sp-select-all-container").show().find("input").prop("disabled", false);
	});

	// Self-cloning
	$(".sp-clone:last").find("select").change(function() {
		$(this).closest(".sp-clone").siblings().find("select").change(function() {
			if($(this).val() == "0") $(this).closest(".sp-clone").remove();
		}).find("option:first").text(localized_strings.remove_text);
		if($(this).val() != "0") {
			$original = $(this).closest(".sp-clone");
			$original.before($original.clone().find("select").attr("name", $original.attr("data-clone-name") + "[]").val($(this).val()).closest(".sp-clone")).attr("data-clone-num", parseInt($original.attr("data-clone-num")) + 1).find("select").val("0").change();
		}
	});

	// Activate self-cloning
	$(".sp-clone:last").find("select").change();

	// Custom value editor
	$(".sp-data-table .sp-default-value").click(function() {
		$(this).hide().siblings(".sp-custom-value").show().find(".sp-custom-value-input").focus();
	});

	// Define custom value editor saving
	$(".sp-data-table .sp-custom-value .sp-custom-value-input").on("saveInput", function() {
		$val = $(this).val();
		if($val == "") $val = $(this).attr("placeholder");
		$(this).closest(".sp-custom-value").hide().siblings(".sp-default-value").show().find(".sp-default-value-input").html($val);
	});

	// Define custom value editor cancellation
	$(".sp-data-table .sp-custom-value .sp-custom-value-input").on("cancelInput", function() {
		$val = $(this).closest(".sp-custom-value").siblings(".sp-default-value").find(".sp-default-value-input").html();
		if($val == $(this).attr("placeholder")) $(this).val("");
		else $(this).val($val);
		$(this).closest(".sp-custom-value").hide().siblings(".sp-default-value").show();
	});

	// Custom value editor save
	$(".sp-data-table .sp-custom-value .sp-save").click(function() {
		$(this).siblings(".sp-custom-value-input").trigger("saveInput");
	});

	// Custom value editor cancel
	$(".sp-data-table .sp-custom-value .sp-cancel").click(function() {
		$(this).siblings(".sp-custom-value-input").trigger("cancelInput");
	});

	// Prevent custom value editor input from submitting form
	$(".sp-data-table .sp-custom-value .sp-custom-value-input").keypress(function(event) {
		if(event.keyCode == 13){
			event.preventDefault();
			$(this).trigger("saveInput");
			return false;
		}
	});

	// Cancel custom value editor form on escape
	$(".sp-data-table .sp-custom-value .sp-custom-value-input").keyup(function(event) {
		if(event.keyCode == 27){
			event.preventDefault();
			$(this).trigger("cancelInput");
			return false;
		}
	});

	// Data table adjustments
	$(".sp-table-adjustments input").change(function() {
		matrix = $(this).attr("data-matrix");
		$el = $(this).closest(".sp-table-adjustments").siblings(".sp-table-values").find("input[data-matrix="+matrix+"]");
		placeholder = $el.attr("data-placeholder");
		current_adjustment = parseFloat($el.attr("data-adjustment"));
		adjustment = parseFloat($(this).val());
		if(! isNaN(adjustment) && adjustment != 0) {
			placeholder = parseFloat(placeholder);
			if(isNaN(placeholder)) placeholder = 0;
			if(isNaN(current_adjustment)) current_adjustment = 0;
			placeholder += adjustment - current_adjustment;
		}
		$el.attr("placeholder", placeholder);
	});

	// Data table keyboard navigation
	$(".sp-data-table tbody tr td input:text").keydown(function(event) {
		if(! $(this).parent().hasClass("chosen-search") && [37,38,39,40].indexOf(event.keyCode) > -1){
			$el = $(this).closest("td");
			var col = $el.parent().children().index($el)+1;
			var row = $el.parent().parent().children().index($el.parent())+1;
			if(event.keyCode == 37){
				if ( $(this).caret().start != 0 )
					return true;
				col -= 1;
			}
			if(event.keyCode == 38){
				row -= 1;
			}
			if(event.keyCode == 39){
				if ( $(this).caret().start != $(this).val().length )
					return true;
				col += 1;
			}
			if(event.keyCode == 40){
				row += 1;
			}
			$el.closest("tbody").find("tr:nth-child("+row+") td:nth-child("+col+") input:text").focus();
		}
	});

	// Prevent data table from submitting form
	$(".sp-data-table tbody tr td input:text").keypress(function(event) {
		if(! $(this).parent().hasClass("chosen-search") && event.keyCode == 13){
			event.preventDefault();
			$el = $(this).closest("td");
			var col = $el.parent().children().index($el)+1;
			var row = $el.parent().parent().children().index($el.parent())+2;
			$el.closest("tbody").find("tr:nth-child("+row+") td:nth-child("+col+") input:text").focus();
			return false;
		}
	});

	// Total stats calculator
	$(".sp-data-table .sp-total input").on("updateTotal", function() {
		index = $(this).parent().index();
		var sum = 0;
		$(this).closest(".sp-data-table").find(".sp-post").each(function() {
			val = $(this).find("td").eq(index).find("input").val();
			if(val == "") {
				val = $(this).find("td").eq(index).find("input").attr("placeholder");
			}
			if($.isNumeric(val)) {
				sum += parseInt(val, 10);
			}
		});
		$(this).attr("placeholder", sum);
	});

	// Activate total stats calculator
	if($(".sp-data-table .sp-total").size()) {
		$(".sp-data-table .sp-post td input").on("keyup", function() {
			$(this).closest(".sp-data-table").find(".sp-total td").eq($(this).parent().index()).find("input").trigger("updateTotal");
		});
	}

	// Trigger total stats calculator
	$(".sp-data-table .sp-total input").trigger("updateTotal");

	// Select all checkboxes
	$(".sp-select-all").change(function() {
		$range = $(this).closest(".sp-select-all-range");
		$range.find("input[type=checkbox]").prop("checked", $(this).prop("checked"));
	});

	// Check if all checkboxes are checked already
	$(".sp-select-all-range").on("checkCheck", function() {
		$(this).each(function() {
			$(this).find(".sp-select-all").prop("checked", $(this).find("input[type=checkbox]:checked:not(.sp-select-all)").length != 0 && $(this).find("input[type=checkbox]:checked:not(.sp-select-all)").length == $(this).find("input[type=checkbox]:visible:not(.sp-select-all)").length);
		});
	});

	// Activate check check when a checkbox is checked
	$(".sp-select-all-range input[type=checkbox]:not(.sp-select-all)").change(function() {
		$(this).closest(".sp-select-all-range").trigger("checkCheck");
	});

	// Activate check check on page load
	$(".sp-select-all-range").trigger("checkCheck");

	// Trigger check check
	$(".sp-data-table").trigger("checkCheck");

	// Sortable tables
	$(".sp-sortable-table tbody").sortable({
		axis: "y"
	});

	// Video embed
	$(".sp-add-video").click(function() {
		$(this).closest("fieldset").hide().siblings(".sp-video-field").show();
		return false;
	});

	// Removing video embed
	$(".sp-remove-video").click(function() {
		$(this).closest("fieldset").hide().siblings(".sp-video-adder").show().siblings(".sp-video-field").find("input").val(null);
		return false;
	});

	// Equation selector
	$(".sp-equation-selector select:last").change(function() {
		$(this).siblings().change(function() {
			if($(this).val() == "") $(this).remove();
		}).find("option:first").text(localized_strings.remove_text);
		if($(this).val() != "") {
			$(this).before($(this).clone().val($(this).val())).val("").change();
		}
	});

	// Trigger equation selector
	$(".sp-equation-selector select:last").change().siblings().change();

	// Order selector
	$(".sp-order-selector select:first").change(function() {
		if($(this).val() == "0") {
			$(this).siblings().prop( "disabled", true );
		} else {
			$(this).siblings().prop( "disabled", false )
		}
	});

	// Trigger order selector
	$(".sp-order-selector select:first").change();

	// Format selector
	$(".sp-format-selector select:first").change(function() {

		$precisionselector = $(".sp-precision-selector input:first");
		$equationselector = $(".sp-equation-selector select");

		// Precision settings
		if($(this).val() == "decimal" || $(this).val() == "time") {
			$precisionselector.prop( "disabled", false );
		} else {
			$precisionselector.prop( "disabled", true )
		}

		// Equation settings
		if($(this).val() == "custom") {
			$equationselector.prop( "disabled", true );
		} else {
			$equationselector.prop( "disabled", false );
		}

	});

	// Trigger format selector
	$(".sp-format-selector select:first").change();

	// Status selector
	$(".sp-status-selector select:first-child").change(function() {

		$subselector = $(this).siblings();

		// Sub settings
		if($(this).val() == "sub") {
			$subselector.show();
		} else {
			$subselector.hide();
		}

	});

	// Trigger status selector
	$(".sp-status-selector select:first-child").change();

	// Preset field modifier
	$(".sp-custom-input-wrapper .preset").click(function() {
		val = $(this).val();
		if(val == "\\c\\u\\s\\t\\o\\m") return true;
		example = $(this).attr("data-example");
		$(this).closest(".sp-custom-input-wrapper").find(".value").val(val).siblings(".example").html(example);
	});

	// Select custom preset when field is brought to focus
	$(".sp-custom-input-wrapper .value").focus(function() {
		$(this).siblings("label").find(".preset").prop("checked", true);
	});

	// Adjust example field when custom preset is entered
	$(".sp-custom-input-wrapper .value").on("keyup", function() {
		val = $(this).val();
		if ( val === undefined ) return true;
		format = $(this).attr("data-example-format");
		example = format.replace("__val__", val);
		$(this).siblings(".example").html(example);
	});

	// Prevent address input from submitting form
	$(".sp-address").keypress(function(event) {
		return event.keyCode != 13;
	});

	// Dashboard countdown
	$("#sportspress_dashboard_status .sp_status_list li.countdown").each(function() {
		var $this = $(this), finalDate = $(this).data('countdown');
		$this.countdown(finalDate, function(event) {
			$this.find('strong').html(event.strftime("%D "+localized_strings.days+" %H:%M:%S"));
		});
	});

});