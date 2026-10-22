<template>
  <div
    class="upload-multi-wrap"
    v-loading="loading"
    element-loading-text="Đang tải ảnh..."
    element-loading-spinner="el-icon-loading"
  >
    <!-- 多图片上传 -->
    <el-upload
      v-if="multiple"
      :key="uploadRenderKey"
      ref="uploader"
      action="'/upload'"
      name="img"
      list-type="picture-card"
      :on-preview="handlePreview"
      :auto-upload="true"
      :on-remove="handleRemove"
      :http-request="request"
      :file-list="uploadList"
      :multiple="true"
      :show-file-list="true"
      :before-upload="beforeUpload"
      accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
    >
      <template slot="file" slot-scope="{ file }">
        <div class="upload-item" :data-uid="String(file.uid)">
          <img class="el-upload-list__item-thumbnail" :src="file.url" alt />
          <span class="el-upload-list__item-actions upload-item-actions">
            <span class="el-upload-list__item-preview upload-no-drag" @click.stop="handlePreview(file)" @mousedown.stop>
              <i class="el-icon-zoom-in"></i>
            </span>
            <span class="el-upload-list__item-delete upload-no-drag" @click.stop="moveUp(file)" @mousedown.stop>
              <i class="el-icon-top"></i>
            </span>
            <span class="el-upload-list__item-delete upload-no-drag" @click.stop="moveDown(file)" @mousedown.stop>
              <i class="el-icon-bottom"></i>
            </span>
            <span class="el-upload-list__item-delete upload-no-drag" @click.stop="triggerReupload(file)" @mousedown.stop>
              <i class="el-icon-refresh"></i>
            </span>
            <span class="el-upload-list__item-delete upload-no-drag" @click.stop="handleRemoveFromActions(file)" @mousedown.stop>
              <i class="el-icon-delete"></i>
            </span>
          </span>
        </div>
      </template>
      <i class="el-icon-plus"></i>
    </el-upload>

    <el-dialog :visible.sync="dialogVisible">
      <img width="100%" :src="dialogImageUrl" alt />
    </el-dialog>
    <input
      ref="singleReuploadInput"
      class="hidden-reupload-input"
      type="file"
      accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
      @change="onReuploadFileSelected"
    />
  </div>
</template>
<script>
import imageCompression from "browser-image-compression";
import Sortable from "sortablejs";
export default {
  name: "uploader",
  props: {
    targetUrl: {
      // 上传地址
      type: String,
      default: "api/upload-image"
    },
    multiple: {
      // 多图开关
      type: Boolean,
      default: true
    },
    value: {
      // 初始图片链接
      default: ""
    },
    title:""
  },
  data() {
    return {
      dialogImageUrl: "",
      uploadList: [],
      dialogVisible: false,
      loading: false,
      uploadingCount: 0,
      pendingReuploadUid: null,
      pendingReuploadUrl: null,
      sortableInstance: null,
      uploadRenderKey: 0,
      syncingList: false
    };
  },
  watch: {
    value: function(val) {
      if (this.syncingList) {
        return;
      }
      const incoming = this.normalizeToUrls(val);
      const current = this.formatImgArr(this.uploadList);
      if (this.sameUrlOrder(incoming, current)) {
        return;
      }
      this.uploadList = this.normalizeToUploadList(val);
      this.$nextTick(() => this.initSortable());
    }
  },
  mounted() {
    this.uploadList = this.normalizeToUploadList(this.value);
    this.$nextTick(() => {
      this.initSortable();
    });
  },
  beforeDestroy() {
    this.destroySortable();
  },
  methods: {
    getslugname(text) {
      var slug = "";
      // Change to lower case
      var titleLower = (text || "").toLowerCase();
      // Letter "e"
      slug = titleLower.replace(/e|é|è|ẽ|ẻ|ẹ|ê|ế|ề|ễ|ể|ệ/gi, "e");
      // Letter "a"
      slug = slug.replace(/a|á|à|ã|ả|ạ|ă|ắ|ằ|ẵ|ẳ|ặ|â|ấ|ầ|ẫ|ẩ|ậ/gi, "a");
      // Letter "o"
      slug = slug.replace(/o|ó|ò|õ|ỏ|ọ|ô|ố|ồ|ỗ|ổ|ộ|ơ|ớ|ờ|ỡ|ở|ợ/gi, "o");
      // Letter "u"
      slug = slug.replace(/u|ú|ù|ũ|ủ|ụ|ư|ứ|ừ|ữ|ử|ự/gi, "u");
      // Letter "d"
      slug = slug.replace(/đ/gi, "d");
      // Trim the last whitespace
      slug = slug.replace(/\s*$/g, "");
      // Change whitespace to "-"
      slug = slug.replace(/\s+/g, "-");
      return slug;
    },
    startUploadLoading() {
      this.uploadingCount += 1;
      this.loading = true;
    },
    stopUploadLoading() {
      this.uploadingCount = Math.max(0, this.uploadingCount - 1);
      this.loading = this.uploadingCount > 0;
    },
    isAllowedImageFile(file) {
      const allowedTypes = ["image/jpeg", "image/png", "image/webp", "image/jpg"];
      if (file.type && allowedTypes.includes(file.type)) {
        return true;
      }
      const ext = (file.name || "").split(".").pop().toLowerCase();
      return ["jpg", "jpeg", "png", "webp"].includes(ext);
    },
    getCompressionOptions(file) {
      const options = {
        maxSizeMB: 3,
        maxWidthOrHeight: 10000,
        useWebWorker: file.type !== "image/webp",
        maxIteration: 10
      };
      if (file.type === "image/webp") {
        options.fileType = "image/webp";
      } else if (file.type === "image/png") {
        options.fileType = "image/png";
      }
      return options;
    },
    compressImageWithFallback(file) {
      return imageCompression(file, this.getCompressionOptions(file)).catch(() => file);
    },
    beforeUpload(file) {
      if (!this.isAllowedImageFile(file)) {
        this.$message.error("Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.");
        return false;
      }
      this.startUploadLoading();
      return true;
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url;
      this.dialogVisible = true;
    },
    handleRemove(file, fileList) {
      this.uploadList = fileList;
      this.emitInput();
    },
    handleRemoveFromActions(file) {
      const index = this.findFileIndex(file);
      if (index < 0) {
        return;
      }
      this.uploadList.splice(index, 1);
      this.emitInput();
      this.$nextTick(() => this.initSortable());
    },
    moveUp(file) {
      const index = this.findFileIndex(file);
      if (index <= 0) {
        return;
      }
      this.swapFiles(index, index - 1);
      this.emitInput();
    },
    moveDown(file) {
      const index = this.findFileIndex(file);
      if (index < 0 || index >= this.uploadList.length - 1) {
        return;
      }
      this.swapFiles(index, index + 1);
      this.emitInput();
    },
    swapFiles(fromIndex, toIndex) {
      const next = [...this.uploadList];
      const temp = next[fromIndex];
      next.splice(fromIndex, 1);
      next.splice(toIndex, 0, temp);
      this.uploadList = next;
    },
    initSortable() {
      this.destroySortable();
      const uploadRef = this.$refs.uploader;
      const rootEl = uploadRef && uploadRef.$el;
      if (!rootEl) {
        return;
      }
      const listEl = rootEl.querySelector(".el-upload-list--picture-card");
      if (!listEl) {
        return;
      }
      this.sortableInstance = Sortable.create(listEl, {
        animation: 150,
        ghostClass: "upload-sortable-ghost",
        chosenClass: "upload-sortable-chosen",
        dragClass: "upload-sortable-drag",
        draggable: ".el-upload-list__item",
        filter: ".upload-no-drag",
        preventOnFilter: true,
        forceFallback: true,
        fallbackOnBody: true,
        fallbackTolerance: 3,
        onEnd: evt => {
          this.applySortableOrder(evt);
        }
      });
    },
    applySortableOrder(evt) {
      const oldIndex = evt.oldIndex;
      const newIndex = evt.newIndex;
      if (oldIndex == null || newIndex == null || oldIndex === newIndex) {
        return;
      }
      if (oldIndex < 0 || newIndex < 0 || oldIndex >= this.uploadList.length || newIndex >= this.uploadList.length) {
        return;
      }
      const next = [...this.uploadList];
      const [moved] = next.splice(oldIndex, 1);
      next.splice(newIndex, 0, moved);
      this.syncingList = true;
      this.uploadList = next;
      this.emitInput();
      this.uploadRenderKey += 1;
      this.$nextTick(() => {
        this.initSortable();
        this.syncingList = false;
      });
    },
    destroySortable() {
      if (this.sortableInstance) {
        this.sortableInstance.destroy();
        this.sortableInstance = null;
      }
    },
    findFileIndex(file) {
      return this.uploadList.findIndex(item => item.uid === file.uid || item.url === file.url);
    },
    triggerReupload(file) {
      this.pendingReuploadUid = file && file.uid != null ? String(file.uid) : null;
      this.pendingReuploadUrl = file && file.url ? file.url : null;
      const input = this.$refs.singleReuploadInput;
      if (input) {
        input.value = "";
        input.click();
      }
    },
    findPendingReuploadIndex() {
      const uid = this.pendingReuploadUid;
      const url = this.pendingReuploadUrl;
      return this.uploadList.findIndex(item => {
        if (uid != null && String(item.uid) === String(uid)) {
          return true;
        }
        return !!(url && item.url === url);
      });
    },
    onReuploadFileSelected(event) {
      const inputFile = event.target.files && event.target.files[0];
      if (!inputFile || (this.pendingReuploadUid == null && !this.pendingReuploadUrl)) {
        this.pendingReuploadUid = null;
        this.pendingReuploadUrl = null;
        return;
      }
      if (!this.isAllowedImageFile(inputFile)) {
        this.$message.error("Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.");
        this.pendingReuploadUid = null;
        this.pendingReuploadUrl = null;
        return;
      }
      const replaceIndex = this.findPendingReuploadIndex();
      this.startUploadLoading();
      const fileWrapper = {
        file: inputFile,
        onError: () => {
          this.pendingReuploadUid = null;
          this.pendingReuploadUrl = null;
        }
      };
      this.uploadAndResolve(fileWrapper)
        .then(uploadedPath => {
          const index = replaceIndex >= 0 ? replaceIndex : this.findPendingReuploadIndex();
          if (index >= 0) {
            this.$set(this.uploadList, index, {
              ...this.uploadList[index],
              name: inputFile.name,
              url: uploadedPath
            });
            this.emitInput();
          }
        })
        .finally(() => {
          this.pendingReuploadUid = null;
          this.pendingReuploadUrl = null;
        });
    },
    request(req) {
      this.uploadAndResolve(req)
        .then(uploadedPath => {
          this.uploadList.push({
            name: req.file.name,
            url: uploadedPath,
            uid: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
          });
          this.emitInput();
          this.$nextTick(() => this.initSortable());
        })
        .catch(() => {
          if (req && req.onError) {
            req.onError();
          }
        });
    },
    uploadAndResolve(req) {
      return this.compressImageWithFallback(req.file).then(res => {
        return new Promise((resolve, reject) => {
          var xhr, formData;
          xhr = new XMLHttpRequest();
          xhr.withCredentials = false;
          xhr.open("POST", __ENV__.link + "api/upload-image");
          xhr.onload = () => {
            var json;
            if (xhr.status !== 200) {
              this.$notify.error({
                message: "HTTP Error: " + xhr.status
              });
              reject(new Error("HTTP Error: " + xhr.status));
              return;
            }

            json = JSON.parse(xhr.responseText);
            resolve(json.path.replace(__ENV__.link, "/"));
          };
          xhr.onerror = () => {
            this.$notify.error({
              message: "Upload failed"
            });
            reject(new Error("Upload failed"));
          };
          formData = new FormData();
          formData.append("img", res, req.file.name);
          formData.append("title_post", this.getslugname(this.title));
          xhr.send(formData);
        });
      }).finally(() => {
        this.stopUploadLoading();
      });
    },
    normalizeToUrls(value) {
      if (!value) {
        return [];
      }
      const arr = Array.isArray(value) ? value : [value];
      return arr
        .map(item => (typeof item === "string" ? item : item && item.url))
        .filter(Boolean);
    },
    sameUrlOrder(left, right) {
      if (left.length !== right.length) {
        return false;
      }
      return left.every((url, index) => url === right[index]);
    },
    makeStableUid(url, index, used) {
      const base = url ? `url:${url}` : `file:${index}`;
      let uid = base;
      let suffix = 1;
      while (used.has(uid)) {
        uid = `${base}#${suffix}`;
        suffix += 1;
      }
      used.add(uid);
      return uid;
    },
    normalizeToUploadList(value) {
      if (!value) {
        return [];
      }
      const arr = Array.isArray(value) ? value : [value];
      const usedUids = new Set();
      const reused = new Set();
      return arr.map((item, index) => {
        const url = typeof item === "string" ? item : item && item.url;
        const existing = this.uploadList.find(file => file.url === url && !reused.has(file.uid));
        if (existing) {
          reused.add(existing.uid);
          usedUids.add(String(existing.uid));
          return {
            ...existing,
            url
          };
        }
        const incomingUid = typeof item === "object" && item && item.uid ? String(item.uid) : "";
        const uid = incomingUid && !usedUids.has(incomingUid)
          ? incomingUid
          : this.makeStableUid(url, index, usedUids);
        usedUids.add(uid);
        return {
          ...(typeof item === "object" && item ? item : {}),
          url,
          uid
        };
      });
    },
    formatImgArr(arr) {
      return arr.map(item => item.url).filter(Boolean);
    },
    emitInput() {
      this.$emit("input", this.formatImgArr(this.uploadList));
    }
  },
  components: {}
};
</script>
<style>
.avatar-uploader .el-upload {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.avatar-uploader .el-upload:hover {
  border-color: #409eff;
}
.avatar-uploader-icon {
  color: #8c939d;
  text-align: center;
}
.avatar {
  display: block;
}
.reupload {
  border-radius: 50%;
  position: absolute;
  color: #fff;
  background-color: #000000;
  opacity: 0.6;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: none;
}
#uploadIcon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: none;
}
.upload-item {
  width: 100%;
  height: 100%;
}
.upload-item-actions span {
  margin: 0 4px;
}
.upload-no-drag {
  cursor: pointer;
}
.hidden-reupload-input {
  display: none;
}
.upload-sortable-ghost {
  opacity: 0.4;
}
.upload-multi-wrap .el-upload-list--picture-card .el-upload-list__item {
  cursor: move;
}
.upload-multi-wrap {
  position: relative;
  min-height: 148px;
}
</style>