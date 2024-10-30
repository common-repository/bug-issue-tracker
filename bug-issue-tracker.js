jQuery(document).ready(function() {
	jQuery("#bug-issue-tracker input[name=bug-issue-tracker-subject], #bug-issue-tracker textarea[name=bug-issue-tracker-quick-comment]").each(function() {
		var a = jQuery(this),
			b = jQuery("#bug-issue-tracker label[for=" + a.attr("name") + "]");
		"" === this.value && b.removeClass("screen-reader-text"), b.click(function() {
			a.focus(), b.addClass("screen-reader-text")
		}), a.blur(function() {
			"" === this.value && b.removeClass("screen-reader-text")
		}), a.focus(function() {
			b.addClass("screen-reader-text")
		})
	})

	var media_uploader;

	jQuery("#bug-issue-tracker .insert-media").click(function(e) {
		return e.preventDefault(), media_uploader = wp.media.frames.file_frame = wp.media({
			title: i18n.attach_file,
			multiple: !1
		}), media_uploader.on("select", function() {
			file = media_uploader.state().get("selection").first().toJSON(), jQuery("#bug-issue-tracker input[name=bug-issue-tracker-attachment-id]").val(file.id), jQuery("#bug-issue-tracker input[name=bug-issue-tracker-attachment-url]").val(file.url), jQuery("#bug-issue-tracker .bug-issue-tracker-attachment").append('<a href="' + file.url + '" target="_blank">' + file.url + "</a>").show()
		}), media_uploader.open(), !1
	});

	jQuery("#bug-issue-tracker .button-primary").click(function() {
		var a = jQuery(this),
			b = jQuery(this).closest("form"),
			c = jQuery(".bug-issue-tracker-status");
		return a.attr("disabled", !0), c.hide().css("background", "#fffbe5"), jQuery.ajax({
			data: "action=bug-issue-tracker&" + b.serialize(),
			dataType: "json",
			type: "POST",
			url: ajaxurl,
			success: function(e) {
				c.show().text(e.message), setTimeout(function() {
					c.css("background", "none")
				}, 1e3), 1 == e.status ? a.attr("disabled", !0) : a.attr("disabled", !1);
			}
		}), !1
	});
});
