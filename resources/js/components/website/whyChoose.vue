<template>
  <div>
    <h3 class="page-title">Quản lý "Tại sao chọn chúng tôi"</h3>
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div
              class="row"
              v-for="(item, key) in objData"
              :key="'why-' + key"
              style="margin-bottom: 8px"
            >
              <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                <strong>Mục #{{ key + 1 }}</strong>
                <label
                  v-if="key !== 0"
                  style="cursor: pointer; margin: 0"
                  title="Xóa mục"
                  @click="removeItem(key)"
                >
                  <vs-icon icon="clear"></vs-icon>
                </label>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Ảnh minh họa</label>
                  <image-upload
                    type="avatar"
                    v-model="item.image"
                    :title="'tai-sao-chon-' + (key + 1)"
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
                    placeholder="VD: Thiết kế nội thất gia đình"
                    class="w-100"
                  />
                  <el-button size="small" @click="showSettingLang(key, 'title')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang[key + '-title']">
                    <div class="form-group" v-for="(langItem, index) in lang" :key="'why-title-' + key + '-' + index">
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
                  <label>Mô tả ngắn</label>
                  <vs-input
                    type="text"
                    v-model="item.description[0].content"
                    size="default"
                    placeholder="Mô tả ngắn về dịch vụ..."
                    class="w-100"
                  />
                  <el-button size="small" @click="showSettingLang(key, 'description')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang[key + '-description']">
                    <div class="form-group" v-for="(langItem, index) in lang" :key="'why-desc-' + key + '-' + index">
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
                  <label>Link (tùy chọn)</label>
                  <vs-input
                    type="text"
                    v-model="item.link"
                    size="default"
                    placeholder="https://... hoặc /duong-dan"
                    class="w-100"
                  />
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
  link: "",
  status: "1",
});

export default {
  name: "whyChoose",
  data() {
    return {
      objData: [defaultItem()],
      lang: [],
      showLang: {},
    };
  },
  methods: {
    ...mapActions(["saveWhyChoose", "listWhyChoose", "listLanguage", "loadings"]),

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
      this.loadings(true);
      this.saveWhyChoose({ data: this.objData })
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
      this.listWhyChoose()
        .then((response) => {
          this.loadings(false);
          const rows = response.data || [];
          this.objData = rows.length
            ? rows.map((r) => ({
                ...defaultItem(),
                image: r.image || "",
                link: r.link || "",
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
