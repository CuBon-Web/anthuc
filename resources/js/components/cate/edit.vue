<template>
  <div>
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-3"><h4 class="card-title">Sửa danh mục sản phẩm</h4></div>
                <div class="col-md-6"></div>
                <div class="col-md-3">
                  </div>
              </div>
              
              
              <!-- <p class="card-description">Basic form elements</p> -->
              <form class="forms-sample">
                <div class="form-group">
                <label>Tên</label>
                  <vs-input
                    class="w-100"
                    v-model="objData.name[0].content"
                    font-size="40px"
                  />
                  <el-button size="small" @click="showSettingLangExist('name')">Đa ngôn ngữ</el-button>
                  <div class="dropLanguage" v-if="showLang.title == true">
                    <div class="form-group" v-for="(item, index) in lang" :key="'cate-name-' + index">
                      <label v-if="index != 0">{{ item.name }}</label>
                      <input v-if="index != 0" type="text" class="w-100 inputlang" v-model="objData.name[index].content" />
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Ảnh đại diện</label>
                  <image-upload
                    v-model="objData.avatar"
                    type="avatar"
                    :title="'danh-muc'"
                  ></image-upload>
                </div>
                <!-- <div class="form-group">
                  <label>Ảnh đại diện</label>
                  <ImageMulti v-model="objData.imagehome" :title="'trang-chu'" />
                </div> -->
                <div class="form-group">
                <label>Nội dung</label>
                 <TinyMce v-model="objData.content[0].content" />
                 <el-button size="small" @click="showSettingLangExist('content')">Đa ngôn ngữ</el-button>
                 <div class="dropLanguage" v-if="showLang.content == true">
                   <div class="form-group" v-for="(item, index) in lang" :key="'cate-content-' + index">
                     <label v-if="index != 0">{{ item.name }}</label>
                     <TinyMce v-if="index != 0" v-model="objData.content[index].content" />
                   </div>
                 </div>
                </div>
                <div class="form-group">
                  <label for="exampleInputName1">Trạng thái</label>
                  <vs-select v-model="objData.status"
                  >
                      <vs-select-item  value="1" text="Hiện" />
                      <vs-select-item  value="0" text="Ẩn" />
                    </vs-select>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="row fixxed">
        <div class="col-12">
          <div class="saveButton">
            <vs-button color="primary" @click="saveEdit()">Cập nhật</vs-button>
          </div>
        </div>
      </div>
    <!-- content-wrapper ends -->
  </div>
</template>

<script>
import { mapActions } from "vuex";
import TinyMce from "../_common/tinymce";
import ImageMulti from "../_common/upload_image_multi";
export default {
  data() {
    return {
      showLang:{
        title:false,
        content:false
      },
      objData: {
        id:this.$route.params.id,
        name: [
          {
            lang_code:'vi',
            content:''
          }
        ],
        content: [
          {
            lang_code:'vi',
            content:''
          }
        ],
        avatar: "",
        imagehome: [],
        status: "",
      },
      lang:[],
      img: "",
      errors:[]
    };
  },
  components: {
    TinyMce,
    ImageMulti,
  },
  methods: {
    ...mapActions(["getInfoCate","saveCategory","listLanguage", "loadings"]),
    nameImage(event) {
      this.objData.avatar = event;
    },
    parseImageForUpload(image) {
      if (!image) {
        return [];
      }
      if (Array.isArray(image)) {
        return image.filter(url => typeof url === "string" && url.trim() !== "");
      }
      try {
        const parsed = JSON.parse(image);
        return Array.isArray(parsed)
          ? parsed.filter(url => typeof url === "string" && url.trim() !== "")
          : (typeof image === "string" && image ? [image] : []);
      } catch (e) {
        return typeof image === "string" && image ? [image] : [];
      }
    },
    showSettingLangExist(value){
        const key = value === 'content' ? 'content' : 'title';
        const list = value === 'content' ? this.objData.content : this.objData.name;
        this.showLang[key] = !this.showLang[key];
          this.lang.forEach((langItem, index) => {
              if(!list[index] && langItem.code != list[0].lang_code){
                  list.push({ lang_code: langItem.code, content: '' })
              }
          });
    },
    parseLang(value) {
      if (Array.isArray(value) && value.length && value[0] && value[0].lang_code) {
        return value;
      }
      if (typeof value === 'string' && value.trim()) {
        try {
          const parsed = JSON.parse(value);
          if (Array.isArray(parsed) && parsed[0] && parsed[0].lang_code) {
            return parsed;
          }
        } catch (e) {}
        return [{ lang_code: 'vi', content: value }];
      }
      return [{ lang_code: 'vi', content: '' }];
    },
    saveEdit() {
      this.errors = [];
      if(this.objData.name[0].content == '') this.errors.push('Tên danh mục không được để trống');
      if (this.errors.length > 0) {
        this.errors.forEach((value, key) => {
          this.$error(value)
        })
        return;
      } else {
        this.loadings(true);
        this.saveCategory(this.objData)
        .then(response => {
            this.loadings(false);
            this.$router.push({ name: "list_category" });
            this.$success("Sửa danh mục thành công");
            this.$route.push({ name: "list_category" });
          })
          .catch(error => {
            this.loadings(false);
            // this.$error('Sửa danh mục thất bại');
          });
      }
    },
    listLang(){
      this.listLanguage().then(response => {
        this.loadings(false);
        this.lang  = response.data
      }).catch(error => {

      })
    },
    getInfoCates(){
      this.loadings(true);
      this.getInfoCate(this.objData).then(response => {
        this.loadings(false);
        if(response.data == null){
          this.objData ={
            id:this.$route.params.id,
            name: "",
            path: "",
            avatar: "",
            status: "",
          }
        }else{
          this.objData = response.data;
          this.objData.name = JSON.parse(response.data.name);
          this.objData.content = this.parseLang(response.data.content);
          this.objData.imagehome = this.parseImageForUpload(response.data.imagehome);
        }
      }).catch(error => {
        console.log(12);
      });
    },
    changeLanguage(data){
      this.getInfoCates();
    }
  },
  mounted() {
    this.loadings(true);
    this.getInfoCates();
    this.listLang();
  }
};
</script>

<style>
</style>