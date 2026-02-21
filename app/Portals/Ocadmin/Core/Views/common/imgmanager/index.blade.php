<div id="modal-image" class="modal">
  <div id="filemanager" class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $lang->heading_title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"></div>
    </div>
  </div>
</div>

<script type="text/javascript">
  // View mode toggle (grid / list), persisted via localStorage
  function applyViewMode(mode) {
      if (mode === 'list') {
          $('#imgmanager-view-grid').hide();
          $('#imgmanager-view-list').show();
          $('#button-view-list').addClass('active');
          $('#button-view-grid').removeClass('active');
      } else {
          $('#imgmanager-view-list').hide();
          $('#imgmanager-view-grid').show();
          $('#button-view-grid').addClass('active');
          $('#button-view-list').removeClass('active');
      }
  }

  // Restore saved view mode after each AJAX load
  function restoreViewMode() {
      var mode = localStorage.getItem('imgmanager_view') || 'grid';
      applyViewMode(mode);
  }

  $('#modal-image').on('click', '#button-view-grid', function () {
      localStorage.setItem('imgmanager_view', 'grid');
      applyViewMode('grid');
  });

  $('#modal-image').on('click', '#button-view-list', function () {
      localStorage.setItem('imgmanager_view', 'list');
      applyViewMode('list');
  });

  $('#modal-image .modal-body').load("{{ route('lang.ocadmin.common.image-manager.list') }}", function () {
      restoreViewMode();
  });

  // Helper: load content and restore view mode
  function loadAndRestore(url) {
      $('#modal-image .modal-body').load(url, function () { restoreViewMode(); });
  }

  $('#modal-image').on('click', '#button-parent', function (e) {
      e.preventDefault();
      loadAndRestore($(this).attr('href'));
  });

  $('#modal-image').on('click', '#button-refresh', function (e) {
      e.preventDefault();
      loadAndRestore($(this).attr('href'));
  });

  $('#modal-image').on('keydown', '#input-search', function (e) {
      if (e.which == 13) {
          $('#button-search').trigger('click');
      }
  });

  $('#modal-image').on('click', '#button-search', function (e) {
      var url = '{{ route('lang.ocadmin.common.image-manager.list') }}';

      var directory = $('#input-directory').val();
      if (directory) {
          url += '?directory=' + encodeURIComponent(directory);
      }

      var filter_name = $('#input-search').val();
      if (filter_name) {
          url += (url.indexOf('?') > -1 ? '&' : '?') + 'filter_name=' + encodeURIComponent(filter_name);
      }

    @if($thumb)
      url += (url.indexOf('?') > -1 ? '&' : '?') + 'thumb={{ $thumb }}';
    @endif

    @if($target)
      url += (url.indexOf('?') > -1 ? '&' : '?') + 'target={{ $target }}';
    @endif

    @if($ckeditor)
      url += (url.indexOf('?') > -1 ? '&' : '?') + 'ckeditor={{ $ckeditor }}';
    @endif

      loadAndRestore(url);
  });

  $('#modal-image').on('click', '#button-upload', function () {
    $('#form-upload').remove();

    $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file[]" value="" multiple="multiple"/></form>');

    $('#form-upload input[name=\'file[]\']').trigger('click');

    $('#form-upload input[name=\'file[]\']').on('change', function () {
        for (var i = 0; i < this.files.length; i++) {
            if ((this.files[i].size / 1024) > {{ $config_file_max_size }}) {
                $(this).val('');
                alert('{{ $lang->error_upload_size }}');
            }
        }
    });

    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }

    timer = setInterval(function () {
        if ($('#form-upload input[name=\'file[]\']').val() !== '') {
            clearInterval(timer);

            var url = '{{ route('lang.ocadmin.common.image-manager.upload') }}';

            var directory = $('#input-directory').val();
            if (directory) {
                url += '?directory=' + encodeURIComponent(directory);
            }

            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('#button-upload').button('loading');
                },
                complete: function () {
                    $('#button-upload').button('reset');
                },
                success: function (json) {
                    if (json['error']) {
                        alert(json['error']);
                    }

                    if (json['success']) {
                        alert(json['success']);
                        $('#button-refresh').trigger('click');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
  });

  $('#modal-image').on('click', '#button-folder', function () {
      $('#modal-folder').slideToggle();
  });

  $('#modal-image').on('click', '#button-create', function () {
      var url = '{{ route('lang.ocadmin.common.image-manager.folder') }}';

      var directory = $('#input-directory').val();
      if (directory) {
          url += '?directory=' + encodeURIComponent(directory);
      }

      $.ajax({
          url: url,
          type: 'post',
          dataType: 'json',
          data: 'folder=' + encodeURIComponent($('#input-folder').val()),
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          beforeSend: function () {
              $('#button-create').button('loading');
          },
          complete: function () {
              $('#button-create').button('reset');
          },
          success: function (json) {
              if (json['error']) {
                  alert(json['error']);
              }

              if (json['success']) {
                  alert(json['success']);
                  $('#button-refresh').trigger('click');
              }
          },
          error: function (xhr, ajaxOptions, thrownError) {
              console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
      });
  });

  $('#modal-image').on('click', '#button-delete', function (e) {
    if (confirm('{{ $lang->text_confirm }}')) {
      $.ajax({
        url: "{{ route('lang.ocadmin.common.image-manager.delete') }}",
        type: 'post',
        dataType: 'json',
        data: $('input[name^=\'path\']:checked'),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            $('#button-delete').button('loading');
        },
        complete: function () {
            $('#button-delete').button('reset');
        },
        success: function (json) {
            if (json['error']) {
                alert(json['error']);
            }

            if (json['success']) {
                alert(json['success']);
                $('#button-refresh').trigger('click');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    }
  });

  $('#modal-image').on('click', 'a.directory', function (e) {
      e.preventDefault();
      loadAndRestore($(this).attr('href'));
  });

  $('#modal-image').on('click', 'a.thumbnail', function (e) {
    e.preventDefault();

    @if($thumb)
      // Grid view: img is child of <a>, path input is in sibling .form-check
      // List view: img is child of <a> in <td>, path input is in first <td> of same <tr>
      var $el = $(this);
      var imgSrc = $el.find('img').attr('src') || $el.attr('href');
      var pathVal = '';

      if ($el.closest('tr').length) {
          // List view
          pathVal = $el.closest('tr').find('input[name^="path"]').val();
      } else {
          // Grid view
          pathVal = $el.closest('.mb-3').find('input[name^="path"]').val();
      }

      $('{{ $thumb }}').attr('src', imgSrc);
      $('{{ $target }}').val(pathVal);
    @endif

    @if($ckeditor)
      CKEDITOR.instances['{{ $ckeditor }}'].insertHtml('<img src="' + $(this).attr('href') + '" alt="" title=""/>');
    @endif

    $('#modal-image').modal('hide');
  });

  $('#modal-image').on('click', '.pagination a', function (e) {
      e.preventDefault();
      loadAndRestore($(this).attr('href'));
  });

  // Column header sort (list view)
  $('#modal-image').on('click', '.imgmanager-sort', function (e) {
      e.preventDefault();

      var newSort = $(this).data('sort');
      var currentSort = $('#input-sort').val();
      var currentOrder = $('#input-order').val();
      var newOrder = (newSort === currentSort && currentOrder === 'asc') ? 'desc' : 'asc';

      var url = new URL($('#button-refresh').attr('href'));
      var params = new URLSearchParams(url.search);

      params.set('sort', newSort);
      params.set('order', newOrder);

      url.search = params.toString();
      $('#button-refresh').attr('href', url.toString());

      loadAndRestore(url.toString());
  });
</script>
