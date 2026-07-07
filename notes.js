let currentNoteId = null;
let currentNote = null;

function filterNotes()
{
    let search =
        document
        .getElementById('search')
        .value
        .toLowerCase();

    document
        .querySelectorAll('.card')
        .forEach(card => {

            let text =
                card.innerText.toLowerCase();

            card.style.display =
                text.includes(search)
                ? ''
                : 'none';
        });
}

async function openNote(id, title)
{
    currentNoteId = id;

    document.getElementById(
        'modalTitle'
    ).textContent = title;

    document.getElementById(
        'modalInfo'
    ).textContent = '';

    document.getElementById(
        'modalBody'
    ).innerHTML =
        '<div class="loader"></div>';

    document.getElementById(
        'modalEditor'
    ).value = '';

    showPreview();

    document.getElementById(
        'noteModal'
    ).style.display = 'block';

    try {

        const response =
            await fetch(
                'view.php?id=' + id
            );

        const note =
            await response.json();

        currentNote = note;


		document.getElementById(
			'modalTitleEditor'
		).value =
			note.title;

		document.getElementById(
			'modalCategoryEditor'
		).value =
			note.category;

        document.getElementById(
            'modalTitle'
        ).textContent =
            note.title;

        document.getElementById(
            'modalInfo'
        ).textContent =
            note.category;

        document.getElementById(
            'modalEditor'
        ).value =
            note.content;

        let imageIndex = 0;
        let imagesHtml = '';

        let textContent = note.content.replace(
            /!\[.*?\]\((.*?)\)/g,
            function() {


                imagesHtml += '<img class="note-image" src="image.php?id=' +
                    id +
                    '&image=' +
                    imageIndex +
                    '" />'



                imageIndex++;

                return '';
            }
        );

        textContent = textContent.replace(
            /\n/g,
            '<br>'
        );

        let html = textContent;

        if (imagesHtml !== '') {

            html +=
                '<div class="note-gallery">' +
                imagesHtml +
                '</div>';
        }

        document.getElementById(
            'modalBody'
        ).innerHTML = html;

    }
    catch(error)
    {
        document.getElementById(
            'modalBody'
        ).innerHTML =
            '<p>Failed to load note.</p>';

        console.error(error);
    }
}

function closeModal()
{
    document.getElementById(
        'noteModal'
    ).style.display = 'none';
}

function openInNextcloud()
{
    window.open(
        '/nextcloud/apps/notes/note/' +
        currentNoteId,
        '_blank'
    );
}

function showEditor()
{
    document.getElementById(
        'modalBody'
    ).style.display = 'none';

    document.getElementById(
        'modalEditor'
    ).style.display = 'block';

    document.getElementById(
        'modalTitleEditor'
    ).style.display = 'block';

    document.getElementById(
        'modalCategoryEditor'
    ).style.display = 'block';
}

function showPreview()
{
    document.getElementById(
        'modalBody'
    ).style.display = 'block';

    document.getElementById(
        'modalEditor'
    ).style.display = 'none';

    document.getElementById(
        'modalTitleEditor'
    ).style.display = 'none';

    document.getElementById(
        'modalCategoryEditor'
    ).style.display = 'none';
}

function openNewNoteModal()
{
    document.getElementById(
        'newNoteTitle'
    ).value = '';

    document.getElementById(
        'newNoteCategory'
    ).value = 'Unsorted';

    document.getElementById(
        'newNoteContent'
    ).value = '';

    document.getElementById(
        'newNoteModal'
    ).style.display = 'block';
}

function closeNewNoteModal()
{
    document.getElementById(
        'newNoteModal'
    ).style.display = 'none';
}

async function saveNewNote()
{
    const title =
        document.getElementById(
            'newNoteTitle'
        ).value;

    const category =
        document.getElementById(
            'newNoteCategory'
        ).value;

    const content =
        document.getElementById(
            'newNoteContent'
        ).value;

    const formData =
        new FormData();

    formData.append(
        'title',
        title
    );

    formData.append(
        'category',
        category
    );

    formData.append(
        'content',
        content
    );

    const response =
        await fetch(
            'save_note.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (result.success)
    {
        closeNewNoteModal();
        location.reload();
    }
    else
    {
        alert(
            'Failed to create note'
        );
    }
}

async function saveCurrentNote()
{
    if (!currentNote)
    {
        return;
    }

    const formData =
        new FormData();

    formData.append(
        'id',
        currentNote.id
    );

	formData.append(
		'title',
		document.getElementById(
			'modalTitleEditor'
		).value
	);


	formData.append(
		'category',
		document.getElementById(
			'modalCategoryEditor'
		).value
	);


    formData.append(
        'content',
        document.getElementById(
            'modalEditor'
        ).value
    );

    const response =
        await fetch(
            'update_note.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (result.success)
    {
        closeModal();
        location.reload();
    }
    else
    {
        alert(
            'Failed to save note'
        );
    }
}

window.onclick = function(event)
{
    const noteModal =
        document.getElementById(
            'noteModal'
        );

    const newNoteModal =
        document.getElementById(
            'newNoteModal'
        );

    if (event.target === noteModal)
    {
        closeModal();
    }

    if (event.target === newNoteModal)
    {
        closeNewNoteModal();
    }
};


async function deleteCurrentNote()
{
    if (!currentNote) {
        return;
    }

    if (
        !confirm(
            'Delete this note?'
        )
    ) {
        return;
    }

    const formData =
        new FormData();

    formData.append(
        'id',
        currentNote.id
    );

    const response =
        await fetch(
            'delete_note.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (result.success)
    {
        closeModal();
        location.reload();
    }
    else
    {
        alert(
            'Failed to delete note'
        );
    }
}