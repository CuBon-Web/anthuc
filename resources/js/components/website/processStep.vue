<template>
  <div>
    <h3 class="page-title">Quản lý "How We Work" (Quy trình làm việc)</h3>
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">

            <div
              class="row"
              v-for="(item, key) in objData"
              :key="'process-' + key"
              style="border:1px solid #eee;border-radius:8px;padding:12px;margin-bottom:16px"
            >
              <div class="col-md-12 mb-2 d-flex align-items-center justify-content-between">
                <strong>Bước {{ key + 1 }}</strong>
                <label
                  v-if="objData.length > 1"
                  style="cursor:pointer;margin:0;color:#e55"
                  title="Xóa bước"
                  @click="removeItem(key)"
                >
                  <vs-icon icon="clear"></vs-icon>
                </label>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Ảnh icon / minh họa</label>
                  <image-upload
                    type="avatar"
                    v-model="item.image"
                    :title="'process-step-' + (key + 1)"
                  ></image-upload>
                </div>
              </div>

              <div class="col-md-9">
                <div class="form-group">
                  <label>Tiêu đề <span class="text-danger">*</span></label>
                  <vs-input
                    class="w-100"
                    v-model="item.title[0].content"
                    :placeholder="'VD: ' + (key + 1 < 10 ? '0' : '') + (key + 1) + '. Tư vấn ban đầu'"
                  />
                  <el-button size="small" @click="showSettingLang(key, 'title')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang[key + '-title']">
                    <div class="form-group" v-for="(langItem, index) in lang" :key="'process-title-' + key + '-' + index">
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
                  <vs-textarea v-model="item.description[0].content" rows="3" />
                  <el-button size="small" @click="showSettingLang(key, 'description')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang[key + '-description']">
                    <div class="form-group" v-for="(langItem, index) in lang" :key="'process-desc-' + key + '-' + index">
                      <label v-if="index != 0">{{ langItem.name }}</label>
                      <textarea
                        v-if="index != 0 && item.description[index]"
                        class="w-100 inputlang"
                        rows="3"
                        v-model="item.description[index].content"
                      ></textarea>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Thứ tự</label>
                      <vs-input type="number" v-model="item.sort" class="w-100" />
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Trạng thái</label>
                      <vs-select v-model="item.status">
                        <vs-select-item value="1" text="Hiện" />
                        <vs-select-item value="0" text="Ẩn" />
                      </vs-select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <vs-button color="success" type="border" @click="addItem">
              <vs-icon icon="add"></vs-icon> Thêm bước
            </vs-button>

          </div>
        </div>
      </div>
    </div>

    <div class="row fixxed">
      <div class="col-12">
        <div class="saveButton">
          <vs-button color="primary" @click="save">Lưu</vs-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';

const emptyLang = () => [{ lang_code: 'vi', content: '' }];

const defaultItem = () => ({
  title: emptyLang(),
  description: emptyLang(),
  image: '',
  sort: 0,
  status: '1',
});

export default {
  name: 'processStep',
  data() {
    return {
      objData: [defaultItem()],
      lang: [],
      showLang: {},
    };
  },
  methods: {
    ...mapActions(['saveProcessStep', 'listProcessStep', 'listLanguage', 'loadings']),
    parseLang(value) {
      if (typeof value === 'string' && value.trim()) {
        try {
          const parsed = JSON.parse(value);
          if (Array.isArray(parsed) && parsed[0] && parsed[0].lang_code) {
            return parsed;
          }
        } catch (e) {}
        return [{ lang_code: 'vi', content: value }];
      }
      if (Array.isArray(value) && value.length && value[0] && value[0].lang_code) {
        return value;
      }
      return emptyLang();
    },
    primaryText(field) {
      if (!field || !field[0]) {
        return '';
      }
      return String(field[0].content || '').trim();
    },
    showSettingLang(key, field) {
      const id = key + '-' + field;
      this.$set(this.showLang, id, !this.showLang[id]);
      const list = this.objData[key][field];
      this.lang.forEach((langItem, index) => {
        if (!list[index] && langItem.code !== list[0].lang_code) {
          list.push({ lang_code: langItem.code, content: '' });
        }
      });
    },
    addItem() {
      this.objData.push(defaultItem());
    },
    removeItem(index) {
      this.objData.splice(index, 1);
    },
    listLang() {
      this.listLanguage({ status: 1 }).then(response => {
        this.lang = response.data || [];
      }).catch(() => {});
    },
    load() {
      this.loadings(true);
      this.listProcessStep().then(response => {
        this.loadings(false);
        const data = response.data || [];
        this.objData = data.length
          ? data.map(d => ({
            title: this.parseLang(d.title),
            description: this.parseLang(d.description),
            image: d.image || '',
            sort: d.sort != null ? String(d.sort) : '0',
            status: d.status != null ? String(d.status) : '1',
          }))
          : [defaultItem()];
        this.showLang = {};
      }).catch(() => { this.loadings(false); });
    },
    save() {
      const invalid = this.objData.some(item => !this.primaryText(item.title));
      if (invalid) {
        this.$error('Tiêu đề không được để trống');
        return;
      }
      this.loadings(true);
      this.saveProcessStep({ items: this.objData }).then(() => {
        this.loadings(false);
        this.$success('Lưu thành công');
        this.load();
      }).catch(() => {
        this.loadings(false);
        this.$error('Lưu thất bại');
      });
    },
  },
  mounted() {
    this.listLang();
    this.load();
  },
};
</script>
