<?php

$pagination = $pagination ?? null;
$pagination_params = $pagination_params ?? [];

if (!empty($pagination) && $pagination['total_pages'] > 1) :

    $current_page = (int)$pagination['page'];
    $total_pages = (int)$pagination['total_pages'];

    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    if (($end_page - $start_page) < 4) {
        if ($start_page === 1) {
            $end_page = min($total_pages, 5);
        } elseif ($end_page === $total_pages) {
            $start_page = max(1, $total_pages - 4);
        }
    }

    $make_page_url = static function ($page) use ($pagination_params) {
        $params = $pagination_params;
        $params['page'] = $page;

        return '?' . http_build_query($params);
    };

?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top">

    <small class="text-muted">
        Menampilkan <?= $pagination['from']; ?>–<?= $pagination['to']; ?>
        dari <?= $pagination['total']; ?> data
    </small>

    <nav aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0">

            <li class="page-item <?= $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link"
                   href="<?= htmlspecialchars($make_page_url($current_page - 1)); ?>"
                   aria-label="Sebelumnya">
                    &laquo;
                </a>
            </li>

            <?php for ($i = $start_page; $i <= $end_page; $i++) : ?>

                <li class="page-item <?= $i === $current_page ? 'active' : ''; ?>">
                    <a class="page-link"
                       href="<?= htmlspecialchars($make_page_url($i)); ?>">
                        <?= $i; ?>
                    </a>
                </li>

            <?php endfor; ?>

            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link"
                   href="<?= htmlspecialchars($make_page_url($current_page + 1)); ?>"
                   aria-label="Berikutnya">
                    &raquo;
                </a>
            </li>

        </ul>
    </nav>

</div>

<?php endif; ?>
