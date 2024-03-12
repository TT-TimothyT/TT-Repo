<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

?>


<!-- TT CUSTOM START -->

<!DOCTYPE html>
<html
  xmlns:v="urn:schemas-microsoft-com:vml"
  xmlns:o="urn:schemas-microsoft-com:office:office"
  lang="en"
>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <!--[if mso
      ]><xml
        ><o:OfficeDocumentSettings
          ><o:PixelsPerInch>96</o:PixelsPerInch
          ><o:AllowPNG /></o:OfficeDocumentSettings></xml
    ><![endif]-->
    <!--[if !mso]><!-->
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;700;900&amp;display=swap"
      rel="stylesheet"
      type="text/css"
    />
    <!--<![endif]-->
    <style>
      * {
        box-sizing: border-box;
      }
      body {
        margin: 0;
        padding: 0;
      }
      a[x-apple-data-detectors] {
        color: inherit !important;
        text-decoration: inherit !important;
      }
      #MessageViewBody a {
        color: inherit;
        text-decoration: none;
      }
      p {
        line-height: inherit;
      }
      .desktop_hide,
      .desktop_hide table {
        mso-hide: all;
        display: none;
        max-height: 0;
        overflow: hidden;
      }
      .image_block img + div {
        display: none;
      }
      @media (max-width: 620px) {
        .mobile_hide {
          display: none;
        }
        .row-content {
          width: 100% !important;
        }
        .stack .column {
          width: 100%;
          display: block;
        }
        .mobile_hide {
          min-height: 0;
          max-height: 0;
          max-width: 0;
          overflow: hidden;
          font-size: 0;
        }
        .desktop_hide,
        .desktop_hide table {
          display: table !important;
          max-height: none !important;
        }
        .reverse {
          display: table;
          width: 100%;
        }
        .reverse .column.last {
          display: table-header-group !important;
        }
        .row-2 td.column.last .border {
          padding: 50px 20px;
          border-top: 0;
          border-right: 0;
          border-bottom: 0;
          border-left: 0;
        }
        .row-2 .column-1 .block-1.text_block td.pad {
          padding: 0 20px 5px !important;
        }
        .row-3 .column-1 .block-1.text_block td.pad {
          padding: 0 10px 10px 0 !important;
        }
        .row-2 .column-1 .border {
          padding: 0 !important;
        }
      }
    </style>
    <!--[if true
      ]><style>
        .forceBgColor {
          background-color: white !important;
        }
      </style><!
    [endif]-->
  </head>
  <body
    class="forceBgColor"
    style="
      background-color: transparent;
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: none;
      text-size-adjust: none;
    "
  >
    <table
      class="nl-container"
      width="100%"
      border="0"
      cellpadding="0"
      cellspacing="0"
      role="presentation"
      style="
        mso-table-lspace: 0;
        mso-table-rspace: 0;
        background-color: transparent;
      "
    >
      <tbody>
        <tr>
          <td>
            <table
              class="row row-1"
              align="center"
              width="100%"
              border="0"
              cellpadding="0"
              cellspacing="0"
              role="presentation"
              style="
                mso-table-lspace: 0;
                mso-table-rspace: 0;
                background-color: #f4f4f4;
              "
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      class="row-content stack"
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="
                        mso-table-lspace: 0;
                        mso-table-rspace: 0;
                        background-color: #f4f4f4;
                        border-radius: 0;
                        color: #000;
                        width: 600px;
                        margin: 0 auto;
                      "
                      width="600"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            width="100%"
                            style="
                              mso-table-lspace: 0;
                              mso-table-rspace: 0;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 10px;
                              padding-top: 10px;
                              vertical-align: top;
                              border-top: 0;
                              border-right: 0;
                              border-bottom: 0;
                              border-left: 0;
                            "
                          >
                            <div
                              class="spacer_block block-1"
                              style="
                                height: 1px;
                                line-height: 1px;
                                font-size: 1px;
                              "
                            >
                              &#8202;
                            </div>
                            <table
                              class="image_block block-2"
                              width="100%"
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              role="presentation"
                              style="mso-table-lspace: 0; mso-table-rspace: 0"
                            >
                              <tr>
                                <td class="pad">
                                  <div
                                    class="alignment"
                                    align="left"
                                    style="line-height: 10px"
                                  >
                                    <div style="max-width: 120px;">
                                      <a
                                        href="https://www.trektravel.com/"
                                        target="_blank"
                                        style="outline: none"
                                        tabindex="-1"
                                        ><img
                                          src="https://d15k2d11r6t6rl.cloudfront.net/public/users/Integrators/669d5713-9b6a-46bb-bd7e-c542cff6dd6a/1849774f52be4b868a111ca688e00096/TT-Logo-Dark.png"
                                          style="
                                            display: block;
                                            height: auto;
                                            border: 0;
                                            width: 100%;
                                            max-width: 120px;
                                          "
                                          width="120"
                                      /></a>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              class="row row-2"
              align="center"
              width="100%"
              border="0"
              cellpadding="0"
              cellspacing="0"
              role="presentation"
              style="
                mso-table-lspace: 0;
                mso-table-rspace: 0;
                background-color: #f4f4f4;
                background-size: auto;
              "
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      class="row-content stack"
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="
                        mso-table-lspace: 0;
                        mso-table-rspace: 0;
                        background-size: auto;
                        background-color: #fff;
                        border-bottom: 4px solid #27a8e1;
                        border-radius: 6px;
                        color: #000;
                        width: 600px;
                        margin: 0 auto;
                      "
                      width="600"
                    >
                      <tbody>
                        <tr class="reverse">
                          <td
                            class="column column-1 last"
                            width="100%"
                            style="
                              mso-table-lspace: 0;
                              mso-table-rspace: 0;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 50px;
                              padding-left: 20px;
                              padding-right: 20px;
                              padding-top: 50px;
                              vertical-align: top;
                              border-top: 0;
                              border-right: 0;
                              border-bottom: 0;
                              border-left: 0;
                            "
                          >
                            <div class="border">
                              <table
                                class="text_block block-1"
                                width="100%"
                                border="0"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                                style="
                                  mso-table-lspace: 0;
                                  mso-table-rspace: 0;
                                  word-break: break-word;
                                "
                              >
                                <tr>
                                  <td
                                    class="pad"
                                    style="
                                      padding-bottom: 20px;
                                      padding-left: 40px;
                                      padding-right: 60px;
                                      padding-top: 20px;
                                    "
                                  >
                                    <div
                                      style="
                                        font-family: Tahoma, Verdana, sans-serif;
                                      "
                                    >
                                      <div
                                        class
                                        style="
                                          font-size: 12px;
                                          font-family: Roboto, Tahoma, Verdana,
                                            Segoe, sans-serif;
                                          mso-line-height-alt: 24px;
                                          color: #666;
                                          line-height: 2;
                                        "
                                      >
                                      <?php /* translators: %s: Customer username */ ?>
                                      <?php $user = get_user_by('login', $user_login ); ?>
<p style="
margin: 0;
font-size: 16px;
mso-line-height-alt: 32px;
"><?php echo sprintf( esc_html__( 'Welcome %s,', 'woocommerce' ), esc_html( $user->first_name ) ); ?></p>
                                        
                                        <p
                                          style="
                                            margin: 0;
                                            font-size: 16px;
                                            mso-line-height-alt: 24px;
                                          "
                                        >
                                          &nbsp;
                                        </p>
                                      
<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
<p  style="
                                        margin: 0;
                                        font-size: 16px;
                                        mso-line-height-alt: 32px;
                                      "><?php printf( esc_html__( 'Let’s embark on a journey crafted just for you. You can customize your information, preferences, and more, by visiting', 'woocommerce'  ) ); ?>
&nbsp;
<a class="link" href="https://trektravel.com/my-account/">My Account</a>
</p>
                                        <p
                                          style="
                                            margin: 0;
                                            font-size: 16px;
                                            mso-line-height-alt: 24px;
                                          "
                                        >
                                          &nbsp;
                                        </p>
                                        <p
                                          style="
                                            margin: 0;
                                            font-size: 16px;
                                            mso-line-height-alt: 32px;
                                          "
                                        >
                                          Let the adventure begin!
                                        </p>
                                        <p
                                          style="
                                            margin: 0;
                                            font-size: 16px;
                                            mso-line-height-alt: 32px;
                                          "
                                        >
                                          Your Friends at Trek Travel
                                        </p>
                                        
                                      </div>
                                    </div>
                                  </td>
                                </tr>
                              </table>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              class="row row-3"
              align="center"
              width="100%"
              border="0"
              cellpadding="0"
              cellspacing="0"
              role="presentation"
              style="
                mso-table-lspace: 0;
                mso-table-rspace: 0;
                background-color: #f4f4f4;
              "
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      class="row-content stack"
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="
                        mso-table-lspace: 0;
                        mso-table-rspace: 0;
                        border-radius: 0;
                        color: #000;
                        width: 600px;
                        margin: 0 auto;
                      "
                      width="600"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            width="100%"
                            style="
                              mso-table-lspace: 0;
                              mso-table-rspace: 0;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 15px;
                              padding-left: 15px;
                              padding-right: 15px;
                              padding-top: 15px;
                              vertical-align: top;
                              border-top: 0;
                              border-right: 0;
                              border-bottom: 0;
                              border-left: 0;
                            "
                          >
                            <table
                              class="text_block block-1"
                              width="100%"
                              border="0"
                              cellpadding="0"
                              cellspacing="0"
                              role="presentation"
                              style="
                                mso-table-lspace: 0;
                                mso-table-rspace: 0;
                                word-break: break-word;
                              "
                            >
                              <tr>
                                <td class="pad">
                                  <div
                                    style="
                                      font-family: Tahoma, Verdana, sans-serif;
                                    "
                                  >
                                    <div
                                      class
                                      style="
                                        font-size: 12px;
                                        font-family: Roboto, Tahoma, Verdana,
                                          Segoe, sans-serif;
                                        mso-line-height-alt: 24px;
                                        color: #666;
                                        line-height: 2;
                                      "
                                    >
                                      <p
                                        style="
                                          margin: 0;
                                          font-size: 12px;
                                          mso-line-height-alt: 24px;
                                        "
                                      >
                                       <span style="font-size: 12px"
                                          >Copyright © 2024 Trek Travel, All
                                          rights reserved.</span
                                        ><br /><span style="font-size: 12px"
                                          >613 Williamson St. Suite 207,
                                          Madison, WI 53703</span
                                        >
                                      </p>
                                      <p
                                        style="
                                          margin: 0;
                                          font-size: 12px;
                                          mso-line-height-alt: 24px;
                                        "
                                      >
                                        &nbsp;
                                      </p>
                                      <p
                                        style="
                                          margin: 0;
                                          font-size: 12px;
                                          mso-line-height-alt: 24px;
                                        "
                                      >
                                        <span
                                          style="
                                            color: #666666;
                                            font-size: 12px;
                                          "
                                          >
                                          <a
                                            href="https://trektravel.com/contact-us/"
                                            target="_blank"
                                            style="
                                              text-decoration: underline;
                                              color: #666666;
                                            "
                                            rel="noopener"
                                            >Contact us</a
                                          ></span
                                        >
                                      </p>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- End -->
  </body>
</html>


<!-- TT CUSTOM EMAIL END -->

<?php