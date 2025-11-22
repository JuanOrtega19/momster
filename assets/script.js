const modal = document.getElementById("uploadModal");
const dropArea = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");
const uploadStatus = document.getElementById("uploadStatus");

const MAX_SIZE = 100 * 1024 * 1024; // 100 MB

function openModal() {
  modal.style.display = "block";
}
function closeModal() {
  modal.style.display = "none";
  uploadStatus.innerHTML = "";
}

dropArea.addEventListener("click", () => fileInput.click());
dropArea.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropArea.style.background = "#e9ecef";
});
dropArea.addEventListener("dragleave", () => (dropArea.style.background = "#fff"));
dropArea.addEventListener("drop", (e) => {
  e.preventDefault();
  dropArea.style.background = "#fff";
  handleFiles(e.dataTransfer.files);
});
fileInput.addEventListener("change", () => handleFiles(fileInput.files));

function handleFiles(files) {
  [...files].forEach((file) => {
    if (file.size > MAX_SIZE) {
      // langsung tampilkan pesan error tanpa upload
      const errorWrap = document.createElement("div");
      errorWrap.innerHTML = `<p style="color:red;">❌ ${file.name} terlalu besar (maksimum 100MB)</p>`;
      uploadStatus.appendChild(errorWrap);
      return;
    }
    uploadFile(file);
  });
}

function uploadFile(file) {
  const formData = new FormData();
  formData.append("file", file);

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "upload.php");

  const progressWrap = document.createElement("div");
  progressWrap.innerHTML = `<p>${file.name}</p>
    <div class="progress"><div class="progress-bar"></div></div>
    <span class="status"></span>`;
  uploadStatus.appendChild(progressWrap);

  const progressBar = progressWrap.querySelector(".progress-bar");
  const status = progressWrap.querySelector(".status");

  xhr.upload.addEventListener("progress", (e) => {
    if (e.lengthComputable) {
      let percent = (e.loaded / e.total) * 100;
      progressBar.style.width = percent + "%";
    }
  });

  xhr.onload = () => {
    if (xhr.status === 200) {
      try {
        const res = JSON.parse(xhr.responseText);
        if (res.status === "success") {
          status.innerHTML = " ✅ Uploaded";
        } else {
          status.innerHTML = " ❌ " + res.message;
          progressBar.style.background = "#dc3545";
        }
      } catch (err) {
        status.innerHTML = " ❌ Server error";
      }
    } else {
      status.innerHTML = " ❌ Upload failed";
    }
  };
  xhr.send(formData);
}
