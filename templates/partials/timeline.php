<ol class="timeline">
    <?php foreach ($history as $h): ?>
        <li>
            <span class="timeline-status"><?= e(ucfirst($h['statut'])) ?></span>
            <time datetime="<?= e(date('c', strtotime($h['date_modif']))) ?>"><?= date_fr($h['date_modif'], true) ?></time>
        </li>
    <?php endforeach; ?>
</ol>
