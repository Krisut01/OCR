<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1e1e1e;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #ffffff;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="file"] {
            background-color: #333;
            color: #e0e0e0;
            border: 1px solid #555;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .dropzone {
            border: 2px dashed #444;
            background: #333;
            border-radius: 5px;
            padding: 20px;
        }
        .image-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .image-item {
            background: #2c2c2c;
            border: 1px solid #444;
            border-radius: 10px;
            padding: 10px;
            width: calc(33% - 20px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .image-item:hover {
            transform: scale(1.05);
        }
        .image-item img {
            width: 100%;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .image-item p {
            margin: 10px 0;
            color: #ccc;
        }
        .image-item form button {
            background-color: #dc3545;
        }
        .image-item form button:hover {
            background-color: #c82333;
        }
        .delete-all-button {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 20px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .delete-all-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Image to text converter by me hehehe  </h1>

        <!-- Regular File Upload Form -->
        <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" id="file-upload-form">
            @csrf
            <input type="file" name="images[]" multiple>
            <button type="submit">Upload</button>
        </form>

        <!-- Dropzone Form -->
        <form action="{{ route('upload') }}" class="dropzone" id="my-dropzone" enctype="multipart/form-data">
            @csrf
        </form>

        <!-- Delete All Button -->
        <button class="delete-all-button" id="delete-all-button">Delete All Images</button>

        <div class="image-list" id="image-list">
            @foreach($images as $image)
                <div class="image-item" data-id="{{ $image->id }}">
                    <img src="{{ asset('storage/' . $image->file_path) }}" alt="Image">
                    <p>{{ $image->text }}</p>
                    <form action="{{ route('delete', $image->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        // Dropzone configuration
        Dropzone.options.myDropzone = {
            acceptedFiles: 'image/*',
            paramName: 'images[]',
            maxFilesize: 2, // MB
            success: function(file, response) {
                response.images.forEach(image => updateImageList(image));
            }
        };

        // Handle pasted images
        document.addEventListener('paste', function(e) {
            var items = e.clipboardData.items;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    var file = items[i].getAsFile();
                    var formData = new FormData();
                    formData.append('images[]', file);

                    fetch("{{ route('upload') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        data.images.forEach(image => updateImageList(image));
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        });

        // Handle form submission
        document.getElementById('file-upload-form').addEventListener('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            fetch("{{ route('upload') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                data.images.forEach(image => updateImageList(image));
            })
            .catch(error => console.error('Error:', error));
        });

        // Handle Delete All action
        document.getElementById('delete-all-button').addEventListener('click', function() {
            fetch("{{ route('deleteAll') }}", {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Clear image list
                document.getElementById('image-list').innerHTML = '';
            })
            .catch(error => console.error('Error:', error));
        });

        function updateImageList(image) {
            const imageList = document.getElementById('image-list');
            const imageItem = document.createElement('div');
            imageItem.className = 'image-item';
            imageItem.dataset.id = image.id;

            imageItem.innerHTML = `
                <img src="${image.file_path}" alt="Image">
                <p>${image.text}</p>
                <form action="{{ route('delete', '') }}/${image.id}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            `;

            imageList.appendChild(imageItem);
        }
    </script>
</body>
</html>
