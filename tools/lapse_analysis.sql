SET @now = '2026-03-02';

-- ============================================================
-- SURE LIFE - SEMI-ANNUAL & ANNUAL CLIENTS LAPSE ANALYSIS
-- Generated: 2026-03-02
-- Semi-Annual: lapse if no payment >= 6 months
-- Annual:      lapse if no payment >= 12 months
-- Reference date = last payment date OR enrollment date if no payment
-- ============================================================

SELECT
    'SEMI-ANNUAL'                                                     AS TermCategory,
    c.Id                                                              AS ClientId,
    c.ContractNumber,
    CONCAT(c.LastName, ', ', c.FirstName, ' ', IFNULL(c.MiddleName,'')) AS FullName,
    pt.Term                                                           AS PaymentTerm,
    pk.Package                                                        AS PackageName,
    c.PackagePrice,
    c.PaymentTermAmount,
    CASE c.Status
        WHEN 1 THEN 'Active'
        WHEN 0 THEN 'Inactive/Lapsed'
        ELSE CAST(c.Status AS CHAR)
    END                                                               AS CurrentDBStatus,
    c.DateCreated                                                     AS EnrollmentDate,
    COUNT(p.Id)                                                       AS TotalPayments,
    MAX(p.Date)                                                       AS LastPaymentDate,
    COALESCE(MAX(p.Date), c.DateCreated)                              AS ReferenceDateUsed,
    TIMESTAMPDIFF(MONTH, COALESCE(MAX(p.Date), c.DateCreated), @now) AS MonthsElapsed,
    TIMESTAMPDIFF(DAY,   COALESCE(MAX(p.Date), c.DateCreated), @now) AS DaysElapsed,
    6                                                                 AS GraceMonths,
    CASE
        WHEN TIMESTAMPDIFF(MONTH, COALESCE(MAX(p.Date), c.DateCreated), @now) >= 6
        THEN 'YES → SHOULD LAPSE'
        ELSE 'NO → STILL ACTIVE'
    END                                                               AS LapseVerdict
FROM tblclient c
LEFT JOIN tblpaymentterm pt ON pt.Id = c.PaymentTermId
LEFT JOIN tblpackage pk     ON pk.Id = c.PackageID
LEFT JOIN tblpayment p
       ON p.ClientId = c.Id
      AND (p.VoidStatus IS NULL OR p.VoidStatus = 0)
      AND (p.approval_status IS NULL OR p.approval_status NOT IN ('rejected','void'))
WHERE c.PaymentTermId IN (3,13,18,24,30,35,40,49,54,59,64,69,74,79,84,86,89,95,100,105,110,115,122,127)
GROUP BY c.Id, c.ContractNumber, c.LastName, c.FirstName, c.MiddleName,
         c.Status, c.DateCreated, c.PaymentTermId, pt.Term, pk.Package,
         c.PackagePrice, c.PaymentTermAmount

UNION ALL

SELECT
    'ANNUAL'                                                          AS TermCategory,
    c.Id                                                              AS ClientId,
    c.ContractNumber,
    CONCAT(c.LastName, ', ', c.FirstName, ' ', IFNULL(c.MiddleName,'')) AS FullName,
    pt.Term                                                           AS PaymentTerm,
    pk.Package                                                        AS PackageName,
    c.PackagePrice,
    c.PaymentTermAmount,
    CASE c.Status
        WHEN 1 THEN 'Active'
        WHEN 0 THEN 'Inactive/Lapsed'
        ELSE CAST(c.Status AS CHAR)
    END                                                               AS CurrentDBStatus,
    c.DateCreated                                                     AS EnrollmentDate,
    COUNT(p.Id)                                                       AS TotalPayments,
    MAX(p.Date)                                                       AS LastPaymentDate,
    COALESCE(MAX(p.Date), c.DateCreated)                              AS ReferenceDateUsed,
    TIMESTAMPDIFF(MONTH, COALESCE(MAX(p.Date), c.DateCreated), @now) AS MonthsElapsed,
    TIMESTAMPDIFF(DAY,   COALESCE(MAX(p.Date), c.DateCreated), @now) AS DaysElapsed,
    12                                                                AS GraceMonths,
    CASE
        WHEN TIMESTAMPDIFF(MONTH, COALESCE(MAX(p.Date), c.DateCreated), @now) >= 12
        THEN 'YES → SHOULD LAPSE'
        ELSE 'NO → STILL ACTIVE'
    END                                                               AS LapseVerdict
FROM tblclient c
LEFT JOIN tblpaymentterm pt ON pt.Id = c.PaymentTermId
LEFT JOIN tblpackage pk     ON pk.Id = c.PackageID
LEFT JOIN tblpayment p
       ON p.ClientId = c.Id
      AND (p.VoidStatus IS NULL OR p.VoidStatus = 0)
      AND (p.approval_status IS NULL OR p.approval_status NOT IN ('rejected','void'))
WHERE c.PaymentTermId IN (2,12,17,23,29,34,39,43,48,53,58,63,68,73,78,82,85,88,91,99,106,111,116,118,121,126)
GROUP BY c.Id, c.ContractNumber, c.LastName, c.FirstName, c.MiddleName,
         c.Status, c.DateCreated, c.PaymentTermId, pt.Term, pk.Package,
         c.PackagePrice, c.PaymentTermAmount

ORDER BY TermCategory, LastName, FirstName;
