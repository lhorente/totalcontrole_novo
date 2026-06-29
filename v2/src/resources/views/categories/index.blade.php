@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Categorias</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}'">Dashboard</a></li>
          <li class="breadcrumb-item active">Categorias</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ url('categories/new') }}" class="btn btn-primary">Adicionar categoria</a>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modal-export" title="Exportar categorias">
              <i class="fas fa-download"></i> Exportar
            </button>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Nome</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $category){?>
                <tr>
                  <td><?php echo $category->id ?>.</td>
                  <td>
                    <a href="{{ url('/categories/edit/') }}/<?php echo $category->id ?>"><?php echo $category->nome ?></a>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

<script>
(function () {
  var exportFmt = 'csv';

  var EXPORT_KEYS    = ['id', 'nome'];
  var EXPORT_HEADERS = ['ID', 'Nome'];

  function getCategoryData() {
    var rows = document.querySelectorAll('table tbody tr');
    var data = [];
    rows.forEach(function (row) {
      var cells = row.querySelectorAll('td');
      data.push({
        id:   cells[0] ? cells[0].textContent.replace('.', '').trim() : '',
        nome: cells[1] ? cells[1].textContent.trim() : '',
      });
    });
    return data;
  }

  function downloadFile(filename, content, mimeType) {
    var blob = new Blob([content], { type: mimeType });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href     = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function buildExportContent(fmt) {
    var data = getCategoryData();
    if (fmt === 'json') {
      return { content: JSON.stringify(data, null, 2), filename: 'categorias.json', mimeType: 'application/json' };
    }
    if (fmt === 'csv') {
      var lines = [EXPORT_HEADERS.map(function (h) { return '"' + h + '"'; }).join(';')];
      data.forEach(function (row) {
        lines.push(EXPORT_KEYS.map(function (k) { return '"' + String(row[k]).replace(/"/g, '""') + '"'; }).join(';'));
      });
      return { content: '\uFEFF' + lines.join('\r\n'), filename: 'categorias.csv', mimeType: 'text/csv;charset=utf-8' };
    }
    var sep   = EXPORT_HEADERS.map(function () { return '---'; });
    var lines = [
      '| ' + EXPORT_HEADERS.join(' | ') + ' |',
      '| ' + sep.join(' | ') + ' |',
    ];
    data.forEach(function (row) {
      lines.push('| ' + EXPORT_KEYS.map(function (k) { return String(row[k]).replace(/\|/g, '\\|'); }).join(' | ') + ' |');
    });
    return { content: lines.join('\n'), filename: 'categorias.md', mimeType: 'text/markdown;charset=utf-8' };
  }

  function showCopyFeedback(el) {
    el.style.display = '';
    setTimeout(function () { el.style.display = 'none'; }, 2000);
  }

  function fallbackCopy(text, feedback) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); if (feedback) { showCopyFeedback(feedback); } } catch (e) {}
    document.body.removeChild(ta);
  }

  document.querySelectorAll('#export-fmt-toggle [data-fmt]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      exportFmt = this.dataset.fmt;
      document.querySelectorAll('#export-fmt-toggle [data-fmt]').forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      document.getElementById('export-copy-feedback').style.display = 'none';
    });
  });

  document.getElementById('export-do-download').addEventListener('click', function () {
    var result = buildExportContent(exportFmt);
    downloadFile(result.filename, result.content, result.mimeType);
    $('#modal-export').modal('hide');
  });

  document.getElementById('export-do-copy').addEventListener('click', function () {
    var result   = buildExportContent(exportFmt);
    var feedback = document.getElementById('export-copy-feedback');
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(result.content)
        .then(function () { showCopyFeedback(feedback); })
        .catch(function () { fallbackCopy(result.content, feedback); });
    } else {
      fallbackCopy(result.content, feedback);
    }
  });
})();
</script>
@endsection
