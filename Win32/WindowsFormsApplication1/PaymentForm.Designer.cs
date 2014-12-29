namespace Win32Payments
{
    partial class PaymentForm
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.cmbIntallments = new System.Windows.Forms.ComboBox();
            this.lblInstallments = new System.Windows.Forms.Label();
            this.btnPay = new System.Windows.Forms.Button();
            this.cmbYear = new System.Windows.Forms.ComboBox();
            this.cmbMonth = new System.Windows.Forms.ComboBox();
            this.txtCVV = new System.Windows.Forms.TextBox();
            this.txtCardNumber = new System.Windows.Forms.TextBox();
            this.txtCardHolder = new System.Windows.Forms.TextBox();
            this.Label4 = new System.Windows.Forms.Label();
            this.Label3 = new System.Windows.Forms.Label();
            this.Label2 = new System.Windows.Forms.Label();
            this.Label1 = new System.Windows.Forms.Label();
            this.SuspendLayout();
            // 
            // cmbIntallments
            // 
            this.cmbIntallments.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.cmbIntallments.FormattingEnabled = true;
            this.cmbIntallments.Location = new System.Drawing.Point(371, 98);
            this.cmbIntallments.Name = "cmbIntallments";
            this.cmbIntallments.Size = new System.Drawing.Size(50, 28);
            this.cmbIntallments.TabIndex = 23;
            this.cmbIntallments.Visible = false;
            // 
            // lblInstallments
            // 
            this.lblInstallments.AutoSize = true;
            this.lblInstallments.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.lblInstallments.Location = new System.Drawing.Point(270, 98);
            this.lblInstallments.Name = "lblInstallments";
            this.lblInstallments.Size = new System.Drawing.Size(95, 20);
            this.lblInstallments.TabIndex = 22;
            this.lblInstallments.Text = "Installments";
            this.lblInstallments.Visible = false;
            // 
            // btnPay
            // 
            this.btnPay.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.btnPay.Location = new System.Drawing.Point(174, 191);
            this.btnPay.Name = "btnPay";
            this.btnPay.Size = new System.Drawing.Size(116, 33);
            this.btnPay.TabIndex = 21;
            this.btnPay.Text = "Pay";
            this.btnPay.UseVisualStyleBackColor = true;
            this.btnPay.Click += new System.EventHandler(this.btnPay_Click);
            // 
            // cmbYear
            // 
            this.cmbYear.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.cmbYear.FormattingEnabled = true;
            this.cmbYear.Items.AddRange(new object[] {
            "2014",
            "2015",
            "2016",
            "2017",
            "2018",
            "2019",
            "2020"});
            this.cmbYear.Location = new System.Drawing.Point(228, 141);
            this.cmbYear.Name = "cmbYear";
            this.cmbYear.Size = new System.Drawing.Size(62, 28);
            this.cmbYear.TabIndex = 20;
            this.cmbYear.Text = "2017";
            // 
            // cmbMonth
            // 
            this.cmbMonth.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.cmbMonth.FormattingEnabled = true;
            this.cmbMonth.Items.AddRange(new object[] {
            "01",
            "02",
            "03",
            "04",
            "05",
            "06",
            "07",
            "08",
            "09",
            "10",
            "11",
            "12"});
            this.cmbMonth.Location = new System.Drawing.Point(172, 141);
            this.cmbMonth.Name = "cmbMonth";
            this.cmbMonth.Size = new System.Drawing.Size(50, 28);
            this.cmbMonth.TabIndex = 19;
            this.cmbMonth.Text = "12";
            // 
            // txtCVV
            // 
            this.txtCVV.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.txtCVV.Location = new System.Drawing.Point(172, 98);
            this.txtCVV.Name = "txtCVV";
            this.txtCVV.Size = new System.Drawing.Size(50, 26);
            this.txtCVV.TabIndex = 18;
            this.txtCVV.Text = "111";
            // 
            // txtCardNumber
            // 
            this.txtCardNumber.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.txtCardNumber.Location = new System.Drawing.Point(172, 55);
            this.txtCardNumber.Name = "txtCardNumber";
            this.txtCardNumber.Size = new System.Drawing.Size(249, 26);
            this.txtCardNumber.TabIndex = 17;
            this.txtCardNumber.Text = "4111111111111111";
            this.txtCardNumber.LostFocus += new System.EventHandler(this.txtCardNumber_LostFocus);
            // 
            // txtCardHolder
            // 
            this.txtCardHolder.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.txtCardHolder.Location = new System.Drawing.Point(172, 12);
            this.txtCardHolder.Name = "txtCardHolder";
            this.txtCardHolder.Size = new System.Drawing.Size(249, 26);
            this.txtCardHolder.TabIndex = 16;
            this.txtCardHolder.Text = "Test Customer";
            // 
            // Label4
            // 
            this.Label4.AutoSize = true;
            this.Label4.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.Label4.Location = new System.Drawing.Point(90, 141);
            this.Label4.Name = "Label4";
            this.Label4.Size = new System.Drawing.Size(51, 20);
            this.Label4.TabIndex = 15;
            this.Label4.Text = "Expiry";
            // 
            // Label3
            // 
            this.Label3.AutoSize = true;
            this.Label3.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.Label3.Location = new System.Drawing.Point(99, 98);
            this.Label3.Name = "Label3";
            this.Label3.Size = new System.Drawing.Size(42, 20);
            this.Label3.TabIndex = 14;
            this.Label3.Text = "CVV";
            // 
            // Label2
            // 
            this.Label2.AutoSize = true;
            this.Label2.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.Label2.Location = new System.Drawing.Point(38, 55);
            this.Label2.Name = "Label2";
            this.Label2.Size = new System.Drawing.Size(103, 20);
            this.Label2.TabIndex = 13;
            this.Label2.Text = "Card Number";
            // 
            // Label1
            // 
            this.Label1.AutoSize = true;
            this.Label1.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(161)));
            this.Label1.Location = new System.Drawing.Point(8, 12);
            this.Label1.Name = "Label1";
            this.Label1.Size = new System.Drawing.Size(133, 20);
            this.Label1.TabIndex = 12;
            this.Label1.Text = "Cardholder Name";
            // 
            // Form1
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(443, 240);
            this.Controls.Add(this.cmbIntallments);
            this.Controls.Add(this.lblInstallments);
            this.Controls.Add(this.btnPay);
            this.Controls.Add(this.cmbYear);
            this.Controls.Add(this.cmbMonth);
            this.Controls.Add(this.txtCVV);
            this.Controls.Add(this.txtCardNumber);
            this.Controls.Add(this.txtCardHolder);
            this.Controls.Add(this.Label4);
            this.Controls.Add(this.Label3);
            this.Controls.Add(this.Label2);
            this.Controls.Add(this.Label1);
            this.Name = "Form1";
            this.Text = "Payment Form";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        internal System.Windows.Forms.ComboBox cmbIntallments;
        internal System.Windows.Forms.Label lblInstallments;
        internal System.Windows.Forms.Button btnPay;
        internal System.Windows.Forms.ComboBox cmbYear;
        internal System.Windows.Forms.ComboBox cmbMonth;
        internal System.Windows.Forms.TextBox txtCVV;
        internal System.Windows.Forms.TextBox txtCardNumber;
        internal System.Windows.Forms.TextBox txtCardHolder;
        internal System.Windows.Forms.Label Label4;
        internal System.Windows.Forms.Label Label3;
        internal System.Windows.Forms.Label Label2;
        internal System.Windows.Forms.Label Label1;
    }
}

