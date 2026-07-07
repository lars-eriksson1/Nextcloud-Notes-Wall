<?php
require_once 'config.php';
require_once 'includes/api.php';

$selectedCategory =
    $_GET['category']
    ?? 'Uncategorized';
$apiNotes = getNotes();

$categoryCounts = [];
$notes = [];

foreach ($apiNotes as $note) {
    $category = trim($note['category'] ?? '');
    if ($category === '') {
        $category = 'Uncategorized';
    }
    if (!isset($categoryCounts[$category])) {
        $categoryCounts[$category] = 0;
    }
    $categoryCounts[$category]++;
}

ksort($categoryCounts);
foreach ($apiNotes as $note) {
    $category = trim($note['category'] ?? '');
    if ($category === '') {
        $category = 'Uncategorized';
    }
	if (
		$selectedCategory !== 'all'
		&& $selectedCategory !== $category
	) {
		continue;
	}
	
    $content = $note['content'] ?? '';
    $preview = preg_replace(
        '/!\[.*?\]\(.*?\)/',
        '',
        $content
    );

    $preview = trim($preview);

    if (mb_strlen($preview) > 250) {
        $preview = mb_substr($preview, 0, 250) . '...';
    }

    $notes[] = [
        'id'       => $note['id'],
        'title'    => $note['title'],
        'category' => $category,
        'preview'  => $preview,
        'modified' => $note['modified'],
		'content'  => $content
    ];
}

usort($notes, function ($a, $b) {
    return $b['modified'] <=> $a['modified'];
});

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notes</title>
<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="layout">
    <div class="sidebar">
        <h2>📝 Notes</h2>
<button
    class="new-note-button"
    onclick="openNewNoteModal();">
    ➕ Add Note
</button>

		<div
			class="category <?php echo ($selectedCategory === 'Uncategorized') ? 'active' : ''; ?>"
			onclick="window.location='index.php?category=Uncategorized';"
			style="cursor:pointer;">

			📌 Active Notes

			<span class="count">
				<?php echo $categoryCounts['Uncategorized'] ?? 0; ?>
			</span>

		</div>

		<div
			class="category <?php echo ($selectedCategory === 'all') ? 'active' : ''; ?>"
			onclick="window.location='index.php?category=all';"
			style="cursor:pointer;">
            All Notes
            <span class="count">
                <?php echo count($apiNotes); ?>
            </span>
        </div>
        <?php foreach ($categoryCounts as $category => $count): ?>
		<?php
		if ($category === 'Uncategorized') {
			continue;
		}
		?>
        <div
            class="category <?php echo ($selectedCategory === $category) ? 'active' : ''; ?>"
            onclick="window.location='index.php?category=<?php echo urlencode($category); ?>';"
            style="cursor:pointer;">

            <?php echo htmlspecialchars($category); ?>
            <span class="count">
                <?php echo $count; ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="main">
        <input
            id="search"
            class="search"
            type="text"
            placeholder="Search notes..."
            onkeyup="filterNotes()">
        <div class="grid">
            <?php foreach ($notes as $note): ?>
            <div
                class="card"
				data-title="<?php echo htmlspecialchars($note['title']); ?>"
                onclick="openNote(<?php echo $note['id']; ?>)"
                style="cursor:pointer;">

                <div class="content">
                    <div class="note-title">
                        <?php echo htmlspecialchars($note['title']); ?>
                    </div>

					<?php
					$image =
						extractFirstImage(
							$note['content']
						);

					if ($image):
					?>
					<img src="image.php?id=<?php echo $note['id']; ?>"
						class="card-image" alt="">
					<?php endif; ?>


					<?php
					$images = extractImages(
						$note['content']
					);

					if (!empty($images)):
					?>

					<div class="card-image-container">
						<?php if (count($images) > 1): ?>
						<div class="image-count">
							+<?php echo count($images) - 1; ?>
						</div>
						<?php endif; ?>
					</div>
					<?php endif; ?>
                    <div class="note-category">
                        <?php echo htmlspecialchars($note['category']); ?>
                    </div>
                    <div class="preview"><?php echo htmlspecialchars($note['preview']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<div id="noteModal" class="modal">

    <div class="modal-content">

        <span
            class="close-modal"
            onclick="closeModal()">
            ×
        </span>


        <h2 id="modalTitle"></h2>

        <div id="modalInfo"></div>

        <hr>

		<label>Title</label>

		<input
			id="modalTitleEditor"
			style="
				width:100%;
				margin-bottom:10px;
				display:none;
			">
		<label>Category</label>

		<input
			id="modalCategoryEditor"
			style="
				width:100%;
				margin-bottom:10px;
				display:none;
			">
        <div id="modalBody"></div>

        <textarea
            id="modalEditor"
            style="
                display:none;
                width:100%;
                min-height:400px;
                resize:vertical;
            ">
        </textarea>

        <br>


		<button
			id="editButton"
			onclick="showEditor()">
			Edit
		</button>

		<button
			id="saveButton"
			onclick="saveCurrentNote()"
			style="display:none;">
			Save
		</button>


		<button
			onclick="deleteCurrentNote()"
			style="
				background:#c62828;
				color:white;
			">

			Delete

		</button>

        <button
            id="modalEditButton"
            onclick="openInNextcloud()">

            Edit in Nextcloud

        </button>

    </div>

</div>

<div id="newNoteModal" class="modal">

    <div class="modal-content">

        <span
            class="close-modal"
            onclick="closeNewNoteModal()">
            ×
        </span>

        <h2>New Note</h2>

        <label>Title</label>

        <input
            id="newNoteTitle"
            style="width:100%;margin-bottom:15px;">

        <label>Category</label>

        <input
            id="newNoteCategory"
            value=""
            style="width:100%;margin-bottom:15px;">

        <label>Content</label>

        <textarea
            id="newNoteContent"
            rows="12"
            style="width:100%;">
        </textarea>

        <br><br>

        <button onclick="saveNewNote()">
            Save Note
        </button>

    </div>

</div>

<script src="notes.js?v=1"></script>

</body>
</html>

