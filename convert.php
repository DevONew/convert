<?php

// input.txt → output.json 변환
$inputFile = 'input.txt';
$outputFile = 'output.json';

$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$data = [];
foreach ($lines as $line) {
    $parsed = json_decode($line, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $data[] = $parsed;
    } else {
        echo " JSON 오류: $line\n";
    }
}

file_put_contents($outputFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "변환 완료 → output.json 생성됨\n";


// 병합할 지역 
$mergeMap = [
    '인천광역시' => [
        ['names' => ['강화군', '옹진군'], 'merged' => '강화군옹진군'],
        ['names' => ['중구', '동구'], 'merged' => '중구동구'],
    ],
    '대구광역시' => [
        ['names' => ['중구', '남구'], 'merged' => '중구남구'],
    ],
    '부산광역시' => [
        ['names' => ['서구', '동구'], 'merged' => '서구동구'],
        ['names' => ['중구', '영도구'], 'merged' => '중구영도구'],
    ],
    '경기도' => [
        ['names' => ['포천시', '가평군'], 'merged' => '포천시가평군'],
        ['names' => ['과천시', '의왕시'], 'merged' => '과천시의왕시'],
        ['names' => ['동두천시', '연천군'], 'merged' => '동두천시연천군'],
    ],
    '충청북도' => [
        ['names' => ['단양군', '제천시'], 'merged' => '단양군제천시'],
        ['names' => ['음성군', '진천군', '증평군'], 'merged' => '음성군진천군증평군'],
        ['names' => ['영동군', '보은군', '옥천군', '괴산군'], 'merged' => '영동군보은군옥천군괴산군'],
    ],
    '충청남도' => [
        ['names' => ['서산시', '태안군'], 'merged' => '서산시태안군'],
        ['names' => ['공주시', '부여군', '청양군'], 'merged' => '공주시부여군청양군'],
        ['names' => ['보령시', '서천군'], 'merged' => '보령시서천군'],
        ['names' => ['금산군', '논산시', '계룡시'], 'merged' => '금산군논산시계룡시'],
        ['names' => ['홍성군', '예산군'], 'merged' => '홍성군예산군'],
    ],
    '전라북도' => [
        ['names' => ['정읍시', '고창군'], 'merged' => '정읍시고창군'],
        ['names' => ['남원시', '임실군', '순창군'], 'merged' => '남원시임실군순창군'],
        ['names' => ['김제시', '부안군'], 'merged' => '김제시부안군'],
        ['names' => ['완주군', '진안군', '무주군', '장수군'], 'merged' => '완주군진안군무주군장수군'],
    ],
    '전라남도' => [
        ['names' => ['순천시', '광양시', '곡성군', '구례군'], 'merged' => '순천시광양시곡성군구례군'],
        ['names' => ['나주시', '화순군'], 'merged' => '나주시화순군'],
        ['names' => ['담양군', '함평군', '영광군', '장성군'], 'merged' => '담양군함평군영광군장성군'],
        ['names' => ['고흥군', '보성군', '장흥군', '강진군'], 'merged' => '고흥군보성군장흥군강진군'],
        ['names' => ['완도군', '해남군', '진도군'], 'merged' => '완도군해남군진도군'],
        ['names' => ['영암군', '무안군', '신안군'], 'merged' => '영암군무안군신안군'],
    ],
    '경상남도' => [
        ['names' => ['통영시', '고성군'], 'merged' => '통영시고성군'],
        ['names' => ['사천시', '하동군', '남해군'], 'merged' => '사천시하동군남해군'],
        ['names' => ['밀양시', '의령군', '함안군', '창녕군'], 'merged' => '밀양시의령군함안군창녕군'],
        ['names' => ['함양군', '산청군', '거창군', '합천군'], 'merged' => '함양군산청군거창군합천군'],
    ],
    '경상북도' => [
        ['names' => ['포항시', '울릉군'], 'merged' => '포항시울릉군'],
        ['names' => ['안동시', '예천군'], 'merged' => '안동시예천군'],
        ['names' => ['영주시', '영양군', '봉화군', '울진군'], 'merged' => '영주시영양군봉화군울진군'],
        ['names' => ['영천시', '청도군'], 'merged' => '영천시청도군'],
        ['names' => ['상주시', '문경시'], 'merged' => '상주시문경시'],
        ['names' => ['고령군', '성주군', '칠곡군'], 'merged' => '고령군성주군칠곡군'],
        ['names' => ['군위군', '의성군', '청송군', '영덕군'], 'merged' => '군위군의성군청송군영덕군'],
    ],
    '강원도' => [
        ['names' => ['동해시', '삼척시', '태백시', '정선군'], 'merged' => '동해시삼척시태백시정선군'],
        ['names' => ['속초시', '고성군', '양양군', '인제군'], 'merged' => '속초시고성군양양군인제군'],
        ['names' => ['홍천군', '횡성군', '영월군', '평창군'], 'merged' => '홍천군횡성군영월군평창군'],
        ['names' => ['철원군', '화천군', '양구군'], 'merged' => '철원군화천군양구군'],
    ],
];


// 병합
$merged = [];

foreach ($data as $row) {
    $sd = $row['sdName'];
    $city = $row['cityName'];
    $found = false;

    foreach ($mergeMap[$sd] ?? [] as $rule) {
        if (in_array($city, $rule['names'])) {
            $key = $sd . '::' . $rule['merged'];
            if (!isset($merged[$key])) {
                $merged[$key] = $row;
                $merged[$key]['cityName'] = $rule['merged'];
            } else {
                foreach (['sunsu', 'tusu', 'yutusu', 'dugsu01', 'dugsu02', 'dugsu03'] as $field) {
                    $merged[$key][$field] += $row[$field];
                }
            }
            $found = true;
            break;
        }
    }

    if (!$found) {
        $key = $sd . '::' . $city;
        $merged[$key] = $row;
    }
}

foreach ($merged as &$row) {
    $row['vote01'] = round($row['dugsu01'] / max(1, $row['yutusu']) * 100, 1);
    $row['vote02'] = round($row['dugsu02'] / max(1, $row['yutusu']) * 100, 1);
    $row['vote03'] = round($row['dugsu03'] / max(1, $row['yutusu']) * 100, 1);
    $row['turnout'] = round($row['tusu'] / max(1, $row['sunsu']) * 100, 1);
}


file_put_contents('merged.json', json_encode(array_values($merged), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo " 변환 & 병합 완료 → merged.json 생성\n";
