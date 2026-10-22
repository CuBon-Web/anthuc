<template>
  <div>
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <h4 class="card-title">Sửa loại tin tức</h4>
                </div>
                <div class="col-md-9"></div>
              </div>

              <!-- <p class="card-description">Basic form elements</p> -->
              <form class="forms-sample">
                <div class="form-group">
                  <vs-input
                    class="w-100"
                    v-model="objData.name[0].content"
                    :class="{ 'is-invalid': submitted && $v.objData.name.$error }"
                    font-size="40px"
                    label-placeholder="Tên danh mục"
                  />
                  <el-button size="small" @click="showSettingLangExist('name')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang.title == true">
                    <div class="form-group" v-for="(item, index) in lang" :key="'blog-type-name-' + index">
                      <label v-if="index != 0">{{ item.name }}</label>
                      <input v-if="index != 0" type="text" class="w-100 inputlang" v-model="objData.name[index].content" />
                    </div>
                  </div>
                  <div
                    v-if="submitted && !$v.objData.name.required"
                    class="noti-err"
                  >Tên không để trống</div>
                </div>
                <div class="form-group">

                  <vs-select class="selectExample" v-model="objData.category_slug" placeholder="Danh mục">
                  <vs-select-item
                    :value="0"
                    text="Danh mục chac"
                  />
                  <vs-select-item
                    :value="item.slug"
                    :text="JSON.parse(item.name)[0].content"
                    v-for="(item,index) in categoryList"
                    :key="'f'+index"
                  />
                </vs-select>
                  <div
                    v-if="submitted && !$v.objData.category_slug.required"
                    class="noti-err"
                  >Danh mục cha không để trống</div>
                </div>
                <div class="form-group">
                  <label for="exampleInputName1">Ảnh đại diện</label>
                  <image-upload v-model="objData.avatar" type="avatar"></image-upload>
                </div>
                <div class="form-group">
                  <label for="exampleInputName1">Trạng thái</label>
                  <vs-select v-model="objData.status"
                  >
                    <vs-select-item  value="1" text="Hiện" />
                    <vs-select-item  value="0" text="Ẩn" />
                  </vs-select>
                </div>
                <vs-button
                  color="success"
                  type="gradient"
                  class="mr-left-45"
                  @click="saveEdit()"
                >Lưu lại</vs-button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <!-- content-wrapper ends -->
  </div>
</template>

<script>
import { mapActions } from "vuex";
import { required } from "vuelidate/lib/validators";
export default {
  data() {
    return {
      showLang: {
        title: false
      },
      objData: {
        language: this.$route.params.language,
        id:this.$route.params.id,
        name: [
          {
            lang_code:'vi',
            content:''
          }
        ],
        category_slug: "",
        avatar: "",
        status: "",
        slug:"",
      },
      categoryList:[],
      lang: [],
      img: "",
      submitted: false
    };
  },
  validations: {
    objData: {
      name: { required },
      category_slug: { required }
    }
  },
  methods: {
    ...mapActions([
      "getInfoTypeBlog",
      "saveTypeCateBlog",
      "listLanguage",
      "loadings","listCateBlog"
    ]),
    showSettingLangExist() {
      this.showLang.title = !this.showLang.title;
      this.lang.forEach((langItem, index) => {
        if (!this.objData.name[index] && langItem.code != this.objData.name[0].lang_code) {
          this.objData.name.push({ lang_code: langItem.code, content: '' });
        }
      });
    },
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
      if (Array.isArray(value) && value.length) {
        return value;
      }
      return [{ lang_code: 'vi', content: '' }];
    },
    nameImage(event) {
      this.objData.avatar = event;
    },
    listCategory() {
      this.loadings(true);
      this.listCateBlog()
      .then(response => {
          this.loadings(false);
          this.categoryList = response.data;
        });
    },
    saveEdit() {
      this.submitted = true;
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      } else {
        this.loadings(true);
        this.saveTypeCateBlog(this.objData)
          .then(response => {
            this.loadings(false);
            this.$success("Sửa danh mục thành công");
            this.$emit("closePopup", false);
          })
          .catch(error => {
            this.loadings(false);
            this.$error("Sửa danh mục thất bại");
          });
      }
    },
    listLang() {
      this.listLanguage()
        .then(response => {
          this.loadings(false);
          this.lang = response.data;
        })
        .catch(error => {});
    },
    getInfoCates() {
      this.loadings(true);
      this.getInfoTypeBlog(this.objData)
        .then(response => {
          this.loadings(false);
          if (response.data == null) {
            this.objData = {
              id: this.$route.params.id,
              name: "",
              category_slug: "",
              avatar: "",
              status: ""
            };
          } else {
            this.objData = response.data;
             this.objData.name = this.parseLang(response.data.name);
          }
        })
        .catch(error => {
          console.log(12);
        });
    }
  },
  mounted() {
    this.loadings(true);
    this.getInfoCates();
    this.listLang();
    this.listCategory();
  }
};
</script>

<style>
</style>