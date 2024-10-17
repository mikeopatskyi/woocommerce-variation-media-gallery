jQuery(document).ready(function ($) {
  // Event handler for Upload/Add button
  $(document).on("click", ".wvmg_upload_button", function (e) {
    e.preventDefault();

    var $button = $(this);
    var $parent = $button.closest(".wc-metabox-content");
    var $list = $parent.find(".wvmg_media_list");
    var $input = $parent.find(".wvmg_media_gallery_input");

    // Create the media frame
    var frame = wp.media({
      title: "Add Media to Gallery",
      button: {
        text: "Add to gallery",
      },
      library: {
        type: ["image", "video"],
      },
      multiple: true,
    });

    // When media is selected, run a callback
    frame.on("select", function () {
      var selection = frame.state().get("selection");
      var ids = $input.val() ? $input.val().split(",") : [];

      selection.each(function (attachment) {
        attachment = attachment.toJSON();
        if (ids.indexOf(attachment.id.toString()) === -1) {
          ids.push(attachment.id);

          // Determine HTML to append based on media type
          var mediaHtml = "";
          if (attachment.type === "video") {
            var video_url = attachment.url;
            mediaHtml =
              '<li data-attachment_id="' +
              attachment.id +
              '"><video width="100" height="100" controls>' +
              '<source src="' +
              video_url +
              '" type="' +
              attachment.mime +
              '">Your browser does not support the video tag.</video>' +
              '<a href="#" class="remove_media">&times;</a></li>';
          } else if (attachment.type === "image") {
            var thumb_url = attachment.sizes.thumbnail
              ? attachment.sizes.thumbnail.url
              : attachment.url;
            mediaHtml =
              '<li data-attachment_id="' +
              attachment.id +
              '"><img src="' +
              thumb_url +
              '" />' +
              '<a href="#" class="remove_media">&times;</a></li>';
          }

          // Append the media element to the list
          $list.append(mediaHtml);
        }
      });

      $input.val(ids.join(",")).trigger("change"); // Trigger change event to enable Save button
    });

    // Open the media frame
    frame.open();
  });

  // Remove media item
  $(document).on("click", ".remove_media", function (e) {
    e.preventDefault();

    var $li = $(this).closest("li");
    var $parent = $li.closest(".wc-metabox-content");
    var $input = $parent.find(".wvmg_media_gallery_input");
    var id_to_remove = $li.data("attachment_id").toString();
    var ids = $input.val().split(",");

    ids = ids.filter(function (id) {
      return id !== id_to_remove;
    });

    $input.val(ids.join(",")).trigger("change"); // Trigger change event to enable Save button
    $li.remove();
  });
});
