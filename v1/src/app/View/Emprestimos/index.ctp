<div class="row">

  <div class="col-md-12">

    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Empréstimos pendentes</h3>
      </div>

      <div class="box-body">
        <div class="row">
          <div class="col-md-12">
            <div class="btn-group" role="group" aria-label="...">
              <?php foreach ($period as $date): ?>
                <a href="<?php echo '?ano='.$date->format("Y").'&mes='.$date->format("n") ?>" class="btn btn-default <?php echo $date->format("Y-n") == "$ano-$mes" ? 'active' : '' ?>"><?php echo $date->format("m/Y") ?></a>
              <?php endforeach ?>
            </div>
          </div>
        </div>

        <br />

        <div class="row">
          <div class="col-md-12">
            <table class="table table-striped">
              <tbody>
                <tr>
                <th>Contato</th>
                <th>Total</th>
                <th>A pagar</th>
                <th>Detalhes</th>
              </tr>
                <?php foreach ($transacoes['emprestimos'] as $emprestimo):?>
              <tr>
                <td><?php echo $emprestimo['nome'] ?></td>
                <td><?php echo number_format($emprestimo['total'],2,",",".") ?></td>
                <td><?php echo number_format($emprestimo['total_pagar'],2,",",".") ?></td>
                <td><a href="/transacoes/<?php echo $ano . "/" . $mes . "?pessoa=".$emprestimo['id']."&tipo=emprestimo" ?>">Detalhes</a></td>
              </tr>
            <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
      </div>

<!--
      <?php foreach ($transacoes['emprestimos'] as $emprestimo):?>
      <div class="row">

        <div class="col-md-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title"><?php echo $emprestimo['nome'] ?></h3>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-6">
                  <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Total</h3>
                    </div>
                    <div class="box-body">
                      <?php echo number_format($emprestimo['total'],2,",",".") ?>
                    </div>
                  </div>
                </div>

                <div class="col-xs-6">
                  <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">A pagar</h3>
                    </div>
                    <div class="box-body">
                      <?php echo number_format($emprestimo['total_pagar'],2,",",".") ?>
                    </div>
                  </div>
                </div>

                <div class="col-xs-12">
                  <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Progresso</h3>
                    </div>
                    <div class="box-body">
                      <div class="progress progress">
                        <div class="progress-bar progress-bar-primary" style="width: <?php echo $emprestimo['progresso'] ?>%">
                          <?php echo $emprestimo['progresso'] ?>%
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xs-6">
                  <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Pago</h3>
                    </div>
                    <div class="box-body">
                      <?php echo number_format($emprestimo['total_pago'],2,",",".") ?>
                    </div>
                  </div>
                </div>-
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach ?>
-->
    </div>
  </div>
</div>
