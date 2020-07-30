# HTTP ___ Change Log

## 1.0.0 - under development

- Initial stable release.
- [#44](https://github.com/laminas/laminas-diactoros/pull/44) fixes an issue whereby the uploaded file size was being provided as an integer string, and causing type errors. The value is now cast to integer before creating an `UploadedFile` instance.
