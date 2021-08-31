<?php

class Persons {

    public function CreateNewPerson($input) {
        $domain = $input['domain'];
        $staffName = strtoupper($input['staff_name']);
        $noKad = $input['no_kad'];
        $namaAhli = strtoupper($input['nama_ahli']);
        $identificationNo = $input['identification_no'];
        $contactNo = $input['contact_no'];
        $alamat = $input['alamat'];
        $referal = $input['referal'];
        $email = $input['email'];
        $tarikhDaftar = date('Y-m-d h:i:s');
        $mLastDate = date('Y-m-d h:i:s');
        $point = 0;
        $status = 0;

        $sql = "INSERT INTO person (person_id,card_no,full_name,identification_no,alamat,contact_no,tarikh_daftar,m_lastdate, point, diurus_oleh,status,domain_daftar,referal,email) "
                . " VALUES (null,:card_no,:full_name,:identification_no,:alamat,:contact_no,:tarikh_daftar,:m_lastdate, :point, :diurus_oleh,:status,:domain_daftar,:referal,:email)";

        $sth = $this->db->prepare($sql);

        $sth->bindParam(':card_no', $noKad);
        $sth->bindParam(':full_name', $namaAhli);
        $sth->bindParam(':identification_no', $identificationNo);
        $sth->bindParam(':alamat', $alamat);
        $sth->bindParam(':contact_no', $contactNo);
        $sth->bindParam(':tarikh_daftar', $tarikhDaftar);
        $sth->bindParam(':m_lastdate', $mLastDate);
        $sth->bindParam(':point', $point);
        $sth->bindParam(':diurus_oleh', $staffName);
        $sth->bindParam(':status', $status);
        $sth->bindParam(':domain_daftar', $domain);
        $sth->bindParam(':referal', $referal);
        $sth->bindParam(':email', $email);
        $sth->execute();
    }

}
