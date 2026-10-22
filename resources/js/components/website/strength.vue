<template>
  <div>
    <h3 class="page-title">Quản lý ưu điểm & thế mạnh</h3>
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div
              class="row"
              v-for="(item, key) in objData"
              :key="'strength-' + key"
              style="margin-bottom: 8px"
            >
              <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                <strong>Mục #{{ key + 1 }}</strong>
                <label
                  v-if="objData.length > 1"
                  style="cursor: pointer; margin: 0"
                  title="Xóa mục"
                  @click="removeItem(key)"
                >
                  <vs-icon icon="clear"></vs-icon>
                </label>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Ảnh</label>
                  <image-upload
                    type="avatar"
                    v-model="item.image"
                    :title="'uu-diem-' + (key + 1)"
                  ></image-upload>
                </div>
              </div>

              <div class="col-md-9">
                <div class="form-group">
                  <label>Tiêu đề <span class="text-danger">*</span></label>
                  <vs-input
                    type="text"
                    v-model="item.title[0].content"
                    size="default"
                    placeholder="VD: Cảm hứng từ thảo mộc Việt"
                    class="w-100"
                  />
                  <el-button size="small" @click="showSettingLang(key, 'title')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang[key + '-title']">
                    <div class="form-group" v-for="(langItem, index) in lang" :key="'strength-title-' + key + '-' + index">
                      <label v-if="index != 0">{{ langItem.name }}</label>
                      <input
                        v-if="index != 0 && item.title[index]"
                        type="text"
                        class="w-100 inputlang"
                        v-model="item.title[index].content"
                      />
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Mô tả</label>
                  <vs-input
                    type="text"
                    v-model="item.description[0].content"
                    size="default"
                    placeholder="Mô tả ngắn"
                    class="w-100"
                  />
                  <el-button size="small" @click="showSettingLang(key, 'description')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang[key + '-description']">
                    <div class="form-group" v-for="(langItem, index) in lang" :key="'strength-desc-' + key + '-' + index">
                      <label v-if="index != 0">{{ langItem.name }}</label>
                      <input
                        v-if="index != 0 && item.description[index]"
                        type="text"
                        class="w-100 inputlang"
                        v-model="item.description[index].content"
                      />
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Icon Bootstrap</label>
                  <vs-input
                    type="text"
                    v-model="item.icon"
                    size="default"
                    placeholder="VD: bi bi-flower2"
                    class="w-100"
                  />
                  <small class="text-muted">
                    Dùng class icon Bootstrap, ví dụ
                    <code>bi bi-flower2</code>, <code>bi bi-heart</code>.
                    <i v-if="isIcon(item.icon)" :class="item.icon" class="ms-2"></i>
                  </small>
                </div>
                <div class="form-group">
                  <label>Trạng thái</label>
                  <vs-select v-model="item.status">
                    <vs-select-item value="1" text="Hiện" />
                    <vs-select-item value="0" text="Ẩn" />
                  </vs-select>
                </div>
              </div>

              <hr style="border: 0.5px solid #04040426; width: 100%; margin: 16px 0" />
            </div>

            <vs-button color="primary" @click="saveItems">Lưu</vs-button>
            <vs-button color="success" @click="addItem">Thêm mục</vs-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from "vuex";

const emptyLang = () => [{ lang_code: "vi", content: "" }];

const defaultItem = () => ({
  title: emptyLang(),
  description: emptyLang(),
  image: "",
  icon: "bi bi-flower2",
  status: "1",
});

export default {
  name: "strength",
  data() {
    return {
      objData: [defaultItem()],
      lang: [],
      showLang: {},
    };
  },
  methods: {
    ...mapActions(["saveStrength", "listStrength", "listLanguage", "loadings"]),
    isIcon(value) {
      return /^bi bi-[a-z0-9-]+$/.test((value || "").trim());
    },
    parseLang(value) {
      if (typeof value === "string" && value.trim()) {
        try {
          const parsed = JSON.parse(value);
          if (Array.isArray(parsed) && parsed[0] && parsed[0].lang_code) {
            return parsed;
          }
        } catch (e) {}
        return [{ lang_code: "vi", content: value }];
      }
      if (Array.isArray(value) && value.length && value[0] && value[0].lang_code) {
        return value;
      }
      return emptyLang();
    },
    primaryText(field) {
      if (!field || !field[0]) {
        return "";
      }
      return String(field[0].content || "").trim();
    },
    showSettingLang(key, field) {
      const id = key + "-" + field;
      this.$set(this.showLang, id, !this.showLang[id]);
      const list = this.objData[key][field];
      this.lang.forEach((langItem, index) => {
        if (!list[index] && langItem.code !== list[0].lang_code) {
          list.push({ lang_code: langItem.code, content: "" });
        }
      });
    },
    saveItems() {
      const invalid = this.objData.findIndex((it) => !this.primaryText(it.title));
      if (invalid !== -1) {
        this.$error(`Mục #${invalid + 1}: Tiêu đề không được để trống.`);
        return;
      }
      const badIcon = this.objData.findIndex((it) => {
        const icon = (it.icon || "").trim();
        return icon && !this.isIcon(icon);
      });
      if (badIcon !== -1) {
        this.$error(`Mục #${badIcon + 1}: Icon phải có dạng bi bi-ten-icon.`);
        return;
      }

      this.loadings(true);
      this.saveStrength({ data: this.objData })
        .then(() => {
          this.loadings(false);
          this.$success("Lưu thành công");
          this.loadItems();
        })
        .catch(() => {
          this.loadings(false);
          this.$error("Lưu thất bại");
        });
    },
    addItem() {
      this.objData.push(defaultItem());
    },
    removeItem(i) {
      this.objData.splice(i, 1);
    },
    listLang() {
      this.listLanguage({ status: 1 })
        .then((response) => {
          this.lang = response.data || [];
        })
        .catch(() => {});
    },
    loadItems() {
      this.loadings(true);
      this.listStrength()
        .then((response) => {
          this.loadings(false);
          const rows = response.data || [];
          this.objData = rows.length
            ? rows.map((r) => ({
                ...defaultItem(),
                image: r.image || "",
                icon: r.icon || "bi bi-flower2",
                status: String(r.status != null ? r.status : 1),
                title: this.parseLang(r.title),
                description: this.parseLang(r.description),
              }))
            : [defaultItem()];
          this.showLang = {};
        })
        .catch(() => {
          this.loadings(false);
        });
    },
  },
  mounted() {
    this.listLang();
    this.loadItems();
  },
};
</script>
